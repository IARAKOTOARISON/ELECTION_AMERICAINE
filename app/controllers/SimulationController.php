<?php
namespace app\controllers;

use app\models\Besoin;
use app\models\Don;
use app\models\Distribution;
use app\models\Historique;

use flight\Engine;

class SimulationController extends BaseController {

    /**
     * Afficher la page de simulation avec dispatch automatique
     */
    public function afficherSimulation() {
        // S'assurer que la session est démarrée pour lire les messages flash
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $besoinModel = new Besoin($this->db);
        $donModel = new Don($this->db);
        $distributionModel = new Distribution($this->db);
        
        // Récupérer les besoins non satisfaits et dons disponibles
        $besoins = $besoinModel->getBesoinsNonSatisfaits();
        $dons = $donModel->getDonsDisponibles();
        
        // Exécuter l'algorithme de dispatch automatique
        $distributionsProposees = $this->executerDispatchAutomatique($besoins, $dons);
        
        // Calculer les statistiques
        $stats = [
            'total_besoins' => count($besoins),
            'total_dons' => count($dons),
            'total_distributions' => count($distributionsProposees),
            'taux_satisfaction' => 0
        ];
        
        // Calculer le taux de satisfaction
        if (count($besoins) > 0) {
            $besoins_satisfaits = 0;
            foreach ($distributionsProposees as $dist) {
                $besoin = $this->trouverBesoin($besoins, $dist['idBesoin']);
                if ($besoin && $dist['quantite_attribuee'] >= $besoin['quantite_restante']) {
                    $besoins_satisfaits++;
                }
            }
            $stats['taux_satisfaction'] = round(($besoins_satisfaits / count($besoins)) * 100);
        }
        
        // Récupérer les messages flash
        $success = $_SESSION['success_message'] ?? null;
        $error = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);
        
        // Afficher la vue
        $this->app->render('simulation', [
            'besoins' => $besoins,
            'dons' => $dons,
            'distributions' => $distributionsProposees,
            'stats' => $stats,
            'success' => $success,
            'error' => $error,
            'baseUrl' => $this->getBaseUrl(),
            'nonce' => $this->app->get('csp_nonce')
        ]);
    }

    /**
     * Confirmer et enregistrer le dispatch en base de données
     */
    public function confirmerDispatch() {
        $besoinModel = new Besoin($this->db);
        $donModel = new Don($this->db);
        $distributionModel = new Distribution($this->db);
        
        try {
            // S'assurer que la session est démarrée pour stocker les messages flash
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            // Début de transaction
            $this->db->beginTransaction();
            
            // Récupérer les besoins et dons
            $besoins = $besoinModel->getBesoinsNonSatisfaits();
            $dons = $donModel->getDonsDisponibles();
            
            // Exécuter le dispatch
            $distributionsProposees = $this->executerDispatchAutomatique($besoins, $dons);
            
            // Enregistrer chaque distribution
            $count = 0;
            foreach ($distributionsProposees as $dist) {
                $data = [
                    'idBesoin' => $dist['idBesoin'],
                    'idVille' => $dist['idVille'],
                    'idDon' => $dist['idDon'],
                    'quantite' => $dist['quantite_attribuee'],
                    // Utiliser le champ correspondant au schéma: idStatusDistribution
                    'idStatusDistribution' => 2, // 2 = Effectué (ou ajuster selon vos valeurs en base)
                    'dateDistribution' => date('Y-m-d H:i:s')
                ];
                
                if ($distributionModel->create($data)) {
                    $count++;
                }
            }

            // Mettre à jour les statuts des besoins selon les distributions enregistrées
            // Si un besoin est entièrement couvert -> idStatus = 3 (satisfait), sinon 2 (partiel)
            $stmt = $this->db->prepare("SELECT b.id, b.quantite, COALESCE(SUM(d.quantite),0) AS distribue FROM besoin b LEFT JOIN distribution d ON b.id = d.idBesoin GROUP BY b.id");
            $stmt->execute();
            $besoinsEtat = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($besoinsEtat as $b) {
                $reste = $b['quantite'] - $b['distribue'];
                $status = ($reste <= 0) ? 3 : (($b['distribue'] > 0) ? 2 : 1);
                // Utiliser la méthode update du modèle Besoin
                $besoinModel->update($b['id'], ['idStatus' => $status]);
            }

            // Mettre à jour les statuts des dons selon les distributions enregistrées
            // Si un don est épuisé -> idStatus = 3 (distribué), sinon 2 (partiel) ou 1 (disponible)
            $stmt2 = $this->db->prepare("SELECT don.id, don.quantite, don.montant, don.idProduit, COALESCE(SUM(distr.quantite),0) AS distribue FROM don LEFT JOIN distribution distr ON don.id = distr.idDon GROUP BY don.id");
            $stmt2->execute();
            $donsEtat = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($donsEtat as $dn) {
                // Choisir la référence (quantite pour nature, montant pour financier)
                $baseValeur = ($dn['idProduit'] !== null) ? ($dn['quantite'] ?? 0) : ($dn['montant'] ?? 0);
                $resteDon = $baseValeur - $dn['distribue'];
                $statusDon = ($resteDon <= 0) ? 3 : (($dn['distribue'] > 0) ? 2 : 1);
                $donModel->update($dn['id'], ['idStatus' => $statusDon]);
            }
            
            // Valider la transaction
            $this->db->commit();
            
            $_SESSION['success_message'] = "$count distribution(s) ont été créées avec succès !";
            
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollBack();
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            $_SESSION['error_message'] = "Erreur lors de la création des distributions : " . $e->getMessage();
        }
        
        // Rediriger vers la page de simulation
        $baseUrl = $this->getBaseUrl();
        $this->app->redirect($baseUrl . '/simulation');
    }

    /**
     * Algorithme de dispatch automatique
     * Match les besoins avec les dons par produit et par ordre chronologique
     */
    private function executerDispatchAutomatique($besoins, $dons) {
        $distributions = [];
        
        // Parcourir chaque besoin (déjà trié par date ASC)
        foreach ($besoins as $besoin) {
            $quantite_restante_besoin = $besoin['quantite_restante'];
            
            if ($quantite_restante_besoin <= 0) {
                continue;
            }
            
            // Chercher des dons correspondants (même produit)
            foreach ($dons as &$don) {
                // Vérifier que c'est le même produit
                if ($don['idProduit'] != $besoin['idProduit']) {
                    continue;
                }
                
                // Vérifier qu'il reste de la quantité dans le don
                if ($don['quantite_restante'] <= 0) {
                    continue;
                }
                
                // Calculer la quantité à attribuer (minimum entre besoin et don restants)
                $quantite_a_attribuer = min($quantite_restante_besoin, $don['quantite_restante']);
                
                // Créer la distribution
                $distributions[] = [
                    'idBesoin' => $besoin['id'],
                    'idVille' => $besoin['idVille'],
                    'idDon' => $don['id'],
                    'ville_nom' => $besoin['ville_nom'],
                    'produit_nom' => $besoin['produit_nom'],
                    'besoin_quantite_demandee' => $besoin['quantite'],
                    'besoin_quantite_restante' => $quantite_restante_besoin,
                    'donateur_nom' => $don['donateur_nom'],
                    'don_quantite_disponible' => $don['quantite_restante'],
                    'quantite_attribuee' => $quantite_a_attribuer,
                    'dateBesoin' => $besoin['dateBesoin'],
                    'dateDon' => $don['dateDon'],
                    'dateDistribution' => date('Y-m-d')
                ];
                
                // Mettre à jour les quantités restantes
                $quantite_restante_besoin -= $quantite_a_attribuer;
                $don['quantite_restante'] -= $quantite_a_attribuer;
                
                // Si le besoin est satisfait, passer au suivant
                if ($quantite_restante_besoin <= 0) {
                    break;
                }
            }
        }
        
        return $distributions;
    }

    /**
     * Trouver un besoin par son ID
     */
    private function trouverBesoin($besoins, $idBesoin) {
        foreach ($besoins as $besoin) {
            if ($besoin['id'] == $idBesoin) {
                return $besoin;
            }
        }
        return null;
    }

    /**
     * Lancer une simulation complète (JSON)
     * Route: GET /api/simulation/lancer ou POST /simulation/lancer
     */
    public function lancerSimulation(): void {
        try {
            $besoinModel = new Besoin($this->db);
            $donModel = new Don($this->db);
            
            // Récupérer les données
            $besoins = $besoinModel->getBesoinsNonSatisfaits();
            $dons = $donModel->getDonsDisponibles();
            
            // Exécuter l'algorithme de dispatch
            $distributionsProposees = $this->executerDispatchAutomatique($besoins, $dons);
            
            // Calculer les statistiques
            $stats = [
                'total_besoins' => count($besoins),
                'total_dons' => count($dons),
                'total_distributions' => count($distributionsProposees),
                'taux_satisfaction' => 0
            ];
            
            // Calculer le taux de satisfaction
            if (count($besoins) > 0) {
                $besoins_satisfaits = 0;
                foreach ($distributionsProposees as $dist) {
                    $besoin = $this->trouverBesoin($besoins, $dist['idBesoin']);
                    if ($besoin && $dist['quantite_attribuee'] >= $besoin['quantite_restante']) {
                        $besoins_satisfaits++;
                    }
                }
                $stats['taux_satisfaction'] = round(($besoins_satisfaits / count($besoins)) * 100);
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'distributions' => $distributionsProposees,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Valider et persister une simulation (JSON ou redirect)
     * Route: POST /simulation/valider
     */
    // public function validerSimulation(): void {
    //     try {
    //         if (session_status() !== PHP_SESSION_ACTIVE) {
    //             session_start();
    //         }

    //         $distributionModel = new Distribution($this->db);
    //         $besoinModel = new Besoin($this->db);
    //         $donModel = new Don($this->db);

    //         // Récupérer les propositions depuis POST (JSON) ou recalculer
    //         $payload = $_POST['distributions'] ?? null;
            
    //         if ($payload) {
    //             $distributionsProposees = json_decode($payload, true);
    //             if (!is_array($distributionsProposees)) {
    //                 throw new \Exception('Format de distributions invalide');
    //             }
    //         } else {
    //             // Recalculer si pas de payload
    //             $besoins = $besoinModel->getBesoinsNonSatisfaits();
    //             $dons = $donModel->getDonsDisponibles();
    //             $distributionsProposees = $this->executerDispatchAutomatique($besoins, $dons);
    //         }

    //         $this->db->beginTransaction();

    //         $count = 0;
    //         foreach ($distributionsProposees as $dist) {
    //             $data = [
    //                 'idBesoin' => $dist['idBesoin'],
    //                 'idVille' => $dist['idVille'],
    //                 'idDon' => $dist['idDon'],
    //                 'quantite' => $dist['quantite_attribuee'] ?? $dist['quantite'] ?? 0,
    //                 'idStatusDistribution' => 2,
    //                 'dateDistribution' => date('Y-m-d H:i:s')
    //             ];

    //             if ($distributionModel->create($data)) {
    //                 $count++;
    //             }
    //         }

    //         // Mettre à jour les statuts
    //         $this->mettreAJourStatuts($besoinModel, $donModel);

    //         $this->db->commit();

    //         // Retourner JSON si requête AJAX
    //         if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    //             header('Content-Type: application/json');
    //             echo json_encode([
    //                 'success' => true,
    //                 'message' => "$count distribution(s) créée(s) avec succès",
    //                 'count' => $count
    //             ]);
    //             exit;
    //         }

    //         $_SESSION['success_message'] = "$count distribution(s) ont été créées avec succès !";
    //         $this->app->redirect($this->getBaseUrl() . '/simulation');

    //     } catch (\Exception $e) {
    //         $this->db->rollBack();
            
    //         if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    //             header('Content-Type: application/json');
    //             http_response_code(500);
    //             echo json_encode([
    //                 'success' => false,
    //                 'message' => $e->getMessage()
    //             ]);
    //             exit;
    //         }

    //         if (session_status() !== PHP_SESSION_ACTIVE) {
    //             session_start();
    //         }
    //         $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
    //         $this->app->redirect($this->getBaseUrl() . '/simulation');
    //     }
    // }

    public function validerSimulation(): void {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            $distributionModel = new Distribution($this->db);
            $besoinModel = new Besoin($this->db);
            $donModel = new Don($this->db);
            $historiqueModel = new Historique($this->db); // AJOUT

            $payload = $_POST['distributions'] ?? null;

            if ($payload) {
                $distributionsProposees = json_decode($payload, true);
                if (!is_array($distributionsProposees)) {
                    throw new \Exception('Format de distributions invalide');
                }
            } else {
                $besoins = $besoinModel->getBesoinsNonSatisfaits();
                $dons = $donModel->getDonsDisponibles();
                $distributionsProposees = $this->executerDispatchAutomatique($besoins, $dons);
            }

            // DÉBUT TRANSACTION
            $this->db->beginTransaction();

            // SAUVEGARDE AVANT MODIFICATION
            $historiqueModel->copierDansHistorique('besoin', 'historique_besoin');
            $historiqueModel->copierDansHistorique('don', 'historique_don');
            $historiqueModel->copierDansHistorique('distribution', 'historique_distribution');

            $count = 0;

            foreach ($distributionsProposees as $dist) {
                $data = [
                    'idBesoin' => $dist['idBesoin'],
                    'idVille' => $dist['idVille'],
                    'idDon' => $dist['idDon'],
                    'quantite' => $dist['quantite_attribuee'] ?? 0,
                    'idStatusDistribution' => 2,
                    'dateDistribution' => date('Y-m-d H:i:s')
                ];

                if ($distributionModel->create($data)) {
                    $count++;
                }
            }

            // Mise à jour des statuts
            $this->mettreAJourStatuts($besoinModel, $donModel);

            $this->db->commit();

            $_SESSION['success_message'] = "$count distribution(s) créée(s) avec succès !";
            $this->app->redirect($this->getBaseUrl() . '/simulation');

        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
            $this->app->redirect($this->getBaseUrl() . '/simulation');
        }
    }


    /**
     * Mettre à jour les statuts des besoins et dons après validation
     */
    private function mettreAJourStatuts(Besoin $besoinModel, Don $donModel): void {
        // Mettre à jour les statuts des besoins
        $stmt = $this->db->prepare("
            SELECT b.id, b.quantite, COALESCE(SUM(d.quantite), 0) AS distribue 
            FROM besoin b 
            LEFT JOIN distribution d ON b.id = d.idBesoin 
            GROUP BY b.id
        ");
        $stmt->execute();
        $besoinsEtat = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($besoinsEtat as $b) {
            $reste = $b['quantite'] - $b['distribue'];
            $status = ($reste <= 0) ? 3 : (($b['distribue'] > 0) ? 2 : 1);
            $besoinModel->update($b['id'], ['idStatus' => $status]);
        }

        // Mettre à jour les statuts des dons
        $stmt2 = $this->db->prepare("
            SELECT don.id, don.quantite, don.montant, don.idProduit, COALESCE(SUM(distr.quantite), 0) AS distribue 
            FROM don 
            LEFT JOIN distribution distr ON don.id = distr.idDon 
            GROUP BY don.id
        ");
        $stmt2->execute();
        $donsEtat = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($donsEtat as $dn) {
            $baseValeur = ($dn['idProduit'] !== null) ? ($dn['quantite'] ?? 0) : ($dn['montant'] ?? 0);
            $resteDon = $baseValeur - $dn['distribue'];
            $statusDon = ($resteDon <= 0) ? 3 : (($dn['distribue'] > 0) ? 2 : 1);
            $donModel->update($dn['id'], ['idStatus' => $statusDon]);
        }
    }
}
