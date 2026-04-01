<?php
namespace app\controllers;

use app\models\Achat;
use app\models\AchatDetails;
use app\models\Besoin;
use app\models\Don;
use app\models\AchatAutoService;
use app\models\Ville;
use flight\Engine;

class AchatController extends BaseController {
    private Achat $achatModel;
    private AchatDetails $achatDetailsModel;
    private Besoin $besoinModel;
    private Don $donModel;
    private AchatAutoService $achatService;

    public function __construct(\PDO $db, Engine $app) {
        parent::__construct($db, $app);
        $this->achatModel = new Achat($db);
        $this->achatDetailsModel = new AchatDetails($db);
        $this->besoinModel = new Besoin($db);
        $this->donModel = new Don($db);
        $this->achatService = new AchatAutoService($db);
    }

    /**
     * Afficher la page principale des achats
     */
    public function afficherPageAchats(): void {
        $achats = $this->achatModel->getAllAchats();
        
        // Charger les villes pour le filtre
        $villeModel = new Ville($this->db);
        $villes = $villeModel->getAllVilles();

        // Appliquer le filtre par ville si présent
        $idVille = isset($_GET['ville']) ? (int)$_GET['ville'] : null;
        if ($idVille) {
            $achats = $this->achatModel->getAchatsByVille($idVille);
        }

        $success = $_SESSION['success_message'] ?? null;
        $error = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        // render the `achatListe` view we created earlier
        $this->app->render('achatListe', [
            'achats' => $achats,
            'villes' => $villes,
            'villeSelectionnee' => $idVille,
            'success' => $success,
            'error' => $error,
            'baseUrl' => $this->getBaseUrl(),
            'nonce' => $this->getNonce()
        ]);
    }

    /**
     * Retourne la liste des achats (JSON) — utile pour API/AJAX
     */
    public function getListeAchats(): void {
        $achats = $this->achatModel->getAllAchats();
        header('Content-Type: application/json');
        echo json_encode($achats);
        exit;
    }

    /**
     * Afficher la page listant les besoins restants pour proposition d'achat
     */
    public function getBesoinsRestantsPage(): void {
        $besoins = $this->besoinModel->getBesoinsRestants();

        $this->app->render('besoinsRestantsPourAchat', [
            'besoins' => $besoins,
            'baseUrl' => $this->getBaseUrl(),
            'nonce' => $this->getNonce()
        ]);
    }

    /**
     * Afficher la page d'achat manuel
     * Permet de sélectionner un don financier, un produit et une quantité
     */
    public function proposerAchatsAuto(): void {
        // Récupérer les dons en argent disponibles
        $donsArgent = $this->donModel->getDonsArgentDisponibles();
        $totalDonsArgent = array_sum(array_column($donsArgent, 'montant_restant'));
        
        // Récupérer le taux de frais depuis les paramètres
        $stmt = $this->db->query("SELECT valeur FROM parametres WHERE cle = 'frais_achat_pourcent'");
        $fraisPourcent = (float)($stmt->fetchColumn() ?: 10);
        
        // Récupérer les besoins restants (pour référence)
        $besoinsRestants = $this->besoinModel->getBesoinsNonSatisfaits();
        
        // Récupérer tous les produits disponibles
        $stmt = $this->db->query("SELECT id, nom, prixUnitaire FROM produit ORDER BY nom");
        $produits = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Récupérer les achats récents (10 derniers)
        $stmt = $this->db->query("
            SELECT a.id, a.date_achat, a.montant_total, a.frais_appliques, 
                   ad.quantite, p.nom as produit_nom
            FROM achat a
            LEFT JOIN achat_details ad ON a.id = ad.id_achat
            LEFT JOIN produit p ON ad.id_produit = p.id
            ORDER BY a.date_achat DESC
            LIMIT 10
        ");
        $achatsRecents = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $success = $_SESSION['success_message'] ?? null;
        $error = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $this->app->render('achatProposition', [
            'donsArgent' => $donsArgent,
            'totalDonsArgent' => $totalDonsArgent,
            'fraisPourcent' => $fraisPourcent,
            'besoinsRestants' => $besoinsRestants,
            'produits' => $produits,
            'achatsRecents' => $achatsRecents,
            'success' => $success,
            'error' => $error,
            'baseUrl' => $this->getBaseUrl(),
            'nonce' => $this->getNonce()
        ]);
    }
    
    /**
     * API JSON pour les propositions d'achat (utilisé par AJAX)
     */
    public function getPropositionsAchatsJson(): void {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $besoins = $this->achatService->getBesoinsPrioritaires($limit);
        $propositions = [];

        foreach ($besoins as $b) {
            $cout = $this->achatService->calculerCoutTotal($b);
            $dons = $this->achatService->verifierDonsDisponibles($b);
            $propositions[] = [
                'idBesoin' => $b['id'] ?? null,
                'idProduit' => $b['idProduit'] ?? null,
                'quantite' => $b['quantite'] ?? 0,
                'coutEstime' => $cout,
                'donsDisponibles' => $dons,
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($propositions);
        exit;
    }

    /**
     * Valider et exécuter les achats automatiques sélectionnés.
     * Accepts either:
     * - POST['propositions'] = JSON array of objects { idBesoin, idDon }
     * - POST['besoin_ids'] = array of besoin IDs (from HTML form)
     */
    public function validerAchatsAuto(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        try {
            // Check for HTML form submission (besoin_ids array)
            $besoinIds = $_POST['besoin_ids'] ?? null;
            
            if ($besoinIds && is_array($besoinIds)) {
                // Handle HTML form submission
                $this->executerAchatsDepuisFormulaire($besoinIds);
                return;
            }
            
            // Handle JSON propositions (legacy/API)
            $payload = $_POST['propositions'] ?? null;
            if (is_null($payload)) throw new \Exception('Aucune proposition reçue.');

            $propositions = json_decode($payload, true);
            if (!is_array($propositions)) throw new \Exception('Format de propositions invalide.');

            $createdAchatIds = [];

            foreach ($propositions as $p) {
                $idBesoin = $p['idBesoin'] ?? null;
                $idDon = $p['idDon'] ?? null;
                if (empty($idBesoin) || empty($idDon)) continue;

                $idAchat = $this->achatService->acheterBesoin($this->besoinModel->getById($idBesoin), (int)$idDon);
                if ($idAchat) {
                    $createdAchatIds[] = $idAchat;
                    // Créer une distribution minimale liant le besoin à l'achat (quantité = besoin.quantite)
                    $besoin = $this->besoinModel->getById($idBesoin);
                    $mapping = [[
                        'idBesoin' => $idBesoin,
                        'idDon' => $idDon,
                        'idVille' => $besoin['idVille'] ?? 1,
                        'quantite' => $besoin['quantite'] ?? 0,
                        'idStatusDistribution' => 2,
                        'dateDistribution' => date('Y-m-d H:i:s')
                    ]];
                    $this->achatService->creerDistributionDepuisAchat($idAchat, $mapping);
                }
            }

            $_SESSION['success_message'] = 'Achats automatiques exécutés: ' . count($createdAchatIds);
        } catch (\Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
        }

        $this->app->redirect($this->getBaseUrl() . '/achats');
    }
    
    /**
     * Exécuter les achats depuis le formulaire HTML (besoin_ids)
     */
    private function executerAchatsDepuisFormulaire(array $besoinIds): void {
        try {
            // Récupérer les dons en argent disponibles
            $donsArgent = $this->donModel->getDonsArgentDisponibles();
            if (empty($donsArgent)) {
                throw new \Exception('Aucun don financier disponible pour effectuer des achats.');
            }
            
            // Récupérer le taux de frais
            $stmt = $this->db->query("SELECT valeur FROM parametres WHERE cle = 'frais_achat_pourcent'");
            $fraisPourcent = (float)($stmt->fetchColumn() ?: 10);
            
            $createdCount = 0;
            $donIndex = 0;
            $montantRestantDon = (float)($donsArgent[$donIndex]['montant_restant'] ?? 0);
            $idDonCourant = (int)($donsArgent[$donIndex]['id'] ?? 0);
            
            $this->db->beginTransaction();
            
            foreach ($besoinIds as $idBesoin) {
                $besoin = $this->besoinModel->getById((int)$idBesoin);
                if (!$besoin) continue;
                
                // Calculer le coût total avec frais
                $prixUnitaire = (float)($besoin['prixUnitaire'] ?? 0);
                $quantite = (float)($besoin['quantite'] ?? 0);
                $cout = $prixUnitaire * $quantite;
                $frais = $cout * ($fraisPourcent / 100);
                $total = $cout + $frais;
                
                // Vérifier si on a assez de budget dans le don courant
                while ($montantRestantDon < $total && $donIndex < count($donsArgent) - 1) {
                    $donIndex++;
                    $montantRestantDon += (float)($donsArgent[$donIndex]['montant_restant'] ?? 0);
                    $idDonCourant = (int)($donsArgent[$donIndex]['id'] ?? 0);
                }
                
                if ($montantRestantDon < $total) {
                    // Pas assez de budget, on s'arrête
                    break;
                }
                
                // Créer l'achat
                $achatData = [
                    'idDon' => $idDonCourant,
                    'montant' => $cout,
                    'frais' => $frais,
                    'dateAchat' => date('Y-m-d H:i:s'),
                ];
                
                $idAchat = $this->achatModel->create($achatData);
                
                if ($idAchat) {
                    // Créer le détail de l'achat
                    $this->achatDetailsModel->create([
                        'idAchat' => $idAchat,
                        'idProduit' => $besoin['idProduit'],
                        'quantite' => $quantite,
                        'prixUnitaire' => $prixUnitaire
                    ]);
                    
                    // Mettre à jour le statut du besoin (satisfait = 2)
                    $this->besoinModel->update((int)$idBesoin, ['idStatusBesoin' => 2]);
                    
                    // Déduire du montant restant
                    $montantRestantDon -= $total;
                    $createdCount++;
                }
            }
            
            $this->db->commit();
            $_SESSION['success_message'] = "$createdCount achat(s) effectué(s) avec succès !";
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['error_message'] = 'Erreur: ' . $e->getMessage();
        }
        
        $this->app->redirect($this->getBaseUrl() . '/achats');
    }

    /**
     * Annuler des propositions en cours (simple suppression côté session)
     */
    public function annulerAchats(): void {
        // Selon implémentation, on pourrait stocker des propositions en session
        unset($_SESSION['propositions_achats']);
        $_SESSION['success_message'] = 'Propositions d\'achats annulées.';
        $this->app->redirect($this->getBaseUrl() . '/achats');
    }

    /**
     * Acheter automatiquement les besoins sélectionnés (JSON)
     * Route: POST /achats/auto
     */
    public function acheterAuto(): void {
        try {
            $besoinIds = $_POST['besoin_ids'] ?? null;
            
            if (is_string($besoinIds)) {
                $besoinIds = json_decode($besoinIds, true);
            }
            
            if (empty($besoinIds) || !is_array($besoinIds)) {
                throw new \Exception('Aucun besoin sélectionné');
            }

            // Utiliser le service d'achat automatique
            $resultat = $this->achatService->acheterBesoins($besoinIds);

            // Retourner JSON si requête AJAX
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode($resultat);
                exit;
            }

            // Sinon redirect avec message flash
            if ($resultat['success']) {
                $_SESSION['success_message'] = $resultat['message'];
            } else {
                $_SESSION['error_message'] = $resultat['message'];
            }
            $this->app->redirect($this->getBaseUrl() . '/achats');

        } catch (\Exception $e) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }

            $_SESSION['error_message'] = $e->getMessage();
            $this->app->redirect($this->getBaseUrl() . '/achats');
        }
    }

    /**
     * Vérifier si un besoin peut être acheté (disponibilité fonds)
     * Route: GET /api/achats/verifier-besoin/{id}
     */
    public function verifierBesoin(int $id): void {
        try {
            $besoin = $this->besoinModel->getById($id);
            
            if (!$besoin) {
                throw new \Exception("Besoin #$id introuvable");
            }

            // Calculer le coût avec frais
            $coutDetails = $this->achatService->calculerCoutAvecFrais($besoin);
            
            // Vérifier la disponibilité
            $disponibilite = $this->achatService->verifierDisponibilite($coutDetails['cout_total']);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'besoin' => [
                    'id' => $besoin['id'],
                    'idProduit' => $besoin['idProduit'],
                    'quantite' => $besoin['quantite'],
                    'idVille' => $besoin['idVille'],
                ],
                'cout' => $coutDetails,
                'disponibilite' => $disponibilite,
                'achat_possible' => $disponibilite['disponible'],
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Filtrer les achats par ville (JSON)
     * Route: GET /api/achats/par-ville/{idVille}
     */
    public function getAchatsParVille(int $idVille): void {
        try {
            $achats = $this->achatModel->getAchatsByVille($idVille);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'achats' => $achats,
                'count' => count($achats)
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
     * Valider un achat manuel
     * Transforme l'argent en matériel :
     * - Diminue le montant du don financier
     * - Crée un nouveau don en nature avec le produit acheté
     * Route: POST /achats/manuel/valider
     */
    public function validerAchatManuel(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        try {
            // Récupérer les données du formulaire
            $idDon = isset($_POST['id_don']) ? (int)$_POST['id_don'] : 0;
            $idProduit = isset($_POST['id_produit']) ? (int)$_POST['id_produit'] : 0;
            $quantite = isset($_POST['quantite']) ? (float)$_POST['quantite'] : 0;
            
            if ($idDon <= 0 || $idProduit <= 0 || $quantite <= 0) {
                throw new \Exception('Données invalides. Veuillez remplir tous les champs.');
            }
            
            // Récupérer le don financier
            $stmt = $this->db->prepare("
                SELECT d.id, d.montant, d.dateDon, d.donateur_nom, d.idStatus,
                       d.montant - COALESCE(SUM(dist.montant), 0) AS montant_restant
                FROM don d
                LEFT JOIN distribution dist ON d.id = dist.idDon
                WHERE d.id = ? AND d.idProduit IS NULL
                GROUP BY d.id
            ");
            $stmt->execute([$idDon]);
            $don = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$don) {
                throw new \Exception('Don financier introuvable.');
            }
            
            // Récupérer le produit
            $stmt = $this->db->prepare("SELECT id, nom, prixUnitaire FROM produit WHERE id = ?");
            $stmt->execute([$idProduit]);
            $produit = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$produit) {
                throw new \Exception('Produit introuvable.');
            }
            
            // Récupérer le taux de frais
            $stmt = $this->db->query("SELECT valeur FROM parametres WHERE cle = 'frais_achat_pourcent'");
            $fraisPourcent = (float)($stmt->fetchColumn() ?: 10);
            
            // Calculer le coût total
            $prixUnitaire = (float)$produit['prixUnitaire'];
            $coutProduits = $prixUnitaire * $quantite;
            $frais = $coutProduits * ($fraisPourcent / 100);
            $totalDebiter = $coutProduits + $frais;
            
            // Vérifier le budget
            $montantRestant = (float)$don['montant_restant'];
            if ($totalDebiter > $montantRestant) {
                throw new \Exception("Budget insuffisant. Montant disponible: " . number_format($montantRestant, 0, ',', ' ') . " Ar");
            }
            
            $this->db->beginTransaction();
            
            // 1. Créer l'enregistrement achat
            $stmt = $this->db->prepare("
                INSERT INTO achat (id_don, date_achat, montant_total, frais_appliques)
                VALUES (?, NOW(), ?, ?)
            ");
            $stmt->execute([$idDon, $coutProduits, $frais]);
            $idAchat = $this->db->lastInsertId();
            
            // 2. Créer le détail de l'achat
            $stmt = $this->db->prepare("
                INSERT INTO achat_details (id_achat, id_produit, quantite, prix_unitaire)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$idAchat, $idProduit, $quantite, $prixUnitaire]);
            
            // 3. Créer un nouveau don en nature avec le produit acheté
            // Ce nouveau don représente le matériel acheté, disponible pour distribution
            $stmt = $this->db->prepare("
                INSERT INTO don (idProduit, quantite, dateDon, idStatus, donateur_nom)
                VALUES (?, ?, NOW(), 1, ?)
            ");
            $donateurNom = 'Achat #' . $idAchat . ' (' . ($don['donateur_nom'] ?? 'Anonyme') . ')';
            $stmt->execute([$idProduit, $quantite, $donateurNom]);
            $idNouveauDon = $this->db->lastInsertId();
            
            // 4. Créer une distribution pour tracer l'utilisation de l'argent
            // Cela débitera automatiquement le don financier via les calculs de montant_restant
            $stmt = $this->db->prepare("
                INSERT INTO distribution (idBesoin, idDon, idVille, montant, dateDistribution, idStatusDistribution, id_achat)
                VALUES ((SELECT id FROM besoin LIMIT 1), ?, 1, ?, NOW(), 2, ?)
            ");
            // Note: idBesoin=1 et idVille=1 sont des valeurs par défaut, 
            // car cet achat n'est pas lié à un besoin spécifique
            // On utilise montant au lieu de quantite pour les dons financiers
            $stmt->execute([$idDon, $totalDebiter, $idAchat]);
            
            $this->db->commit();
            
            $_SESSION['success_message'] = "Achat effectué avec succès ! " .
                number_format($quantite, 0, ',', ' ') . " " . $produit['nom'] . " achetés pour " .
                number_format($totalDebiter, 0, ',', ' ') . " Ar (dont " . 
                number_format($frais, 0, ',', ' ') . " Ar de frais). " .
                "Un nouveau don en nature a été créé (ID: $idNouveauDon).";
            
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $_SESSION['error_message'] = 'Erreur: ' . $e->getMessage();
        }
        
        $this->app->redirect($this->getBaseUrl() . '/achats/proposer');
    }
}
