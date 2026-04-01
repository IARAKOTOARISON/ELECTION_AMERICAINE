<?php
namespace app\models;

/**
 * DispatchSimulator — Moteur de simulation pour dispatch de dons vers besoins
 * Classe principale pour simuler et valider les distributions
 */
class DispatchSimulator {
    private \PDO $db;
    private Besoin $besoinModel;
    private Don $donModel;
    private Parametres $parametresModel;

    public function __construct(\PDO $db) {
        $this->db = $db;
        $this->besoinModel = new Besoin($db);
        $this->donModel = new Don($db);
        $this->parametresModel = new Parametres($db);
    }

    /**
     * Simuler un dispatch complet — retourne les propositions sans exécuter
     * @return array Résultat de simulation avec propositions
     */
    public function simulerComplet(): array {
        $besoins = $this->getBesoinsNonSatisfaits();
        $dons = $this->getDonsDisponibles();
        
        $propositions = [];
        $donsUtilises = [];
        $besoinsCouverts = [];
        $totalValeur = 0;
        $totalFrais = 0;

        // Récupérer le taux de frais
        $tauxFrais = (float)$this->parametresModel->getFrais() ?: 0;

        foreach ($besoins as $besoin) {
            $idBesoin = $besoin['id'];
            $quantiteRestante = $besoin['quantite_restante'] ?? $besoin['quantite'];
            $idProduit = $besoin['idProduit'];

            // Chercher d'abord les dons en nature du même produit
            foreach ($dons as $key => $don) {
                if (isset($donsUtilises[$don['id']])) continue;
                
                // Don en nature correspondant au produit
                if (!empty($don['idProduit']) && $don['idProduit'] == $idProduit) {
                    $qteDisponible = $don['quantite_restante'] ?? $don['quantite'] ?? 0;
                    $qteADistribuer = min($quantiteRestante, $qteDisponible);
                    
                    if ($qteADistribuer > 0) {
                        $propositions[] = [
                            'type' => 'nature',
                            'idBesoin' => $idBesoin,
                            'idDon' => $don['id'],
                            'idVille' => $besoin['idVille'],
                            'quantite' => $qteADistribuer,
                            'produit_nom' => $besoin['produit_nom'] ?? '',
                            'ville_nom' => $besoin['ville_nom'] ?? '',
                        ];
                        $quantiteRestante -= $qteADistribuer;
                        $donsUtilises[$don['id']] = true;
                    }
                }
                
                if ($quantiteRestante <= 0) {
                    $besoinsCouverts[] = $idBesoin;
                    break;
                }
            }

            // Si besoin restant, chercher dons argent pour achat
            if ($quantiteRestante > 0) {
                $coutAchat = $this->simulerAchatsAuto($besoin, $quantiteRestante, $tauxFrais);
                if ($coutAchat) {
                    $propositions[] = $coutAchat['proposition'];
                    $totalValeur += $coutAchat['montant'];
                    $totalFrais += $coutAchat['frais'];
                    if ($coutAchat['couvre_entierement']) {
                        $besoinsCouverts[] = $idBesoin;
                    }
                }
            }
        }

        return [
            'success' => true,
            'propositions' => $propositions,
            'besoins_couverts' => count($besoinsCouverts),
            'besoins_total' => count($besoins),
            'total_valeur' => $totalValeur,
            'total_frais' => $totalFrais,
            'taux_frais' => $tauxFrais,
        ];
    }

    /**
     * Récupérer les besoins non satisfaits
     * @return array
     */
    public function getBesoinsNonSatisfaits(): array {
        return $this->besoinModel->getBesoinsNonSatisfaits();
    }

    /**
     * Récupérer les dons disponibles
     * @return array
     */
    public function getDonsDisponibles(): array {
        return $this->donModel->getDonsDisponibles();
    }

    /**
     * Simuler un dispatch en nature uniquement
     * @return array
     */
    public function simulerDispatchNature(): array {
        $besoins = $this->getBesoinsNonSatisfaits();
        $dons = $this->donModel->getDonsDisponibles();
        
        $propositions = [];
        $donsUtilises = [];

        foreach ($besoins as $besoin) {
            $idProduit = $besoin['idProduit'];
            $quantiteRestante = $besoin['quantite_restante'] ?? $besoin['quantite'];

            foreach ($dons as $don) {
                if (isset($donsUtilises[$don['id']])) continue;
                if (empty($don['idProduit']) || $don['idProduit'] != $idProduit) continue;

                $qteDisponible = $don['quantite_restante'] ?? $don['quantite'] ?? 0;
                $qteADistribuer = min($quantiteRestante, $qteDisponible);

                if ($qteADistribuer > 0) {
                    $propositions[] = [
                        'type' => 'nature',
                        'idBesoin' => $besoin['id'],
                        'idDon' => $don['id'],
                        'idVille' => $besoin['idVille'],
                        'quantite' => $qteADistribuer,
                        'produit_nom' => $besoin['produit_nom'] ?? '',
                        'ville_nom' => $besoin['ville_nom'] ?? '',
                    ];
                    $quantiteRestante -= $qteADistribuer;
                    $donsUtilises[$don['id']] = true;
                }

                if ($quantiteRestante <= 0) break;
            }
        }

        return [
            'success' => true,
            'propositions' => $propositions,
            'count' => count($propositions),
        ];
    }

    /**
     * Simuler des achats automatiques avec calcul de frais
     * @param array $besoin
     * @param float $quantite
     * @param float $tauxFrais
     * @return array|null
     */
    public function simulerAchatsAuto(array $besoin, float $quantite, float $tauxFrais = 0): ?array {
        // Calculer le coût d'achat
        $prixUnitaire = $this->getPrixProduit($besoin['idProduit']);
        $montantBase = $quantite * $prixUnitaire;
        $frais = $montantBase * ($tauxFrais / 100);
        $montantTotal = $montantBase + $frais;

        // Chercher un don argent disponible
        $donsArgent = $this->donModel->getDonsArgentDisponibles();
        foreach ($donsArgent as $don) {
            $montantDisponible = $don['montant_restante'] ?? $don['montant'] ?? 0;
            if ($montantDisponible >= $montantTotal) {
                return [
                    'proposition' => [
                        'type' => 'achat',
                        'idBesoin' => $besoin['id'],
                        'idDon' => $don['id'],
                        'idVille' => $besoin['idVille'],
                        'quantite' => $quantite,
                        'montant' => $montantBase,
                        'frais' => $frais,
                        'total' => $montantTotal,
                        'produit_nom' => $besoin['produit_nom'] ?? '',
                        'ville_nom' => $besoin['ville_nom'] ?? '',
                    ],
                    'montant' => $montantBase,
                    'frais' => $frais,
                    'couvre_entierement' => true,
                ];
            }
        }

        return null;
    }

    /**
     * Récupérer le prix unitaire d'un produit
     * @param int $idProduit
     * @return float
     */
    private function getPrixProduit(int $idProduit): float {
        try {
            $stmt = $this->db->prepare("SELECT prixUnitaire FROM produit WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $idProduit]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? (float)$row['prixUnitaire'] : 0;
        } catch (\PDOException $e) {
            return 0;
        }
    }

    /**
     * Valider et exécuter une simulation — créer les distributions réelles
     * @param array $propositions Liste de propositions à exécuter
     * @return array Résultat avec succès/échec
     */
    public function validerSimulation(array $propositions): array {
        if (empty($propositions)) {
            return ['success' => false, 'message' => 'Aucune proposition à valider'];
        }

        try {
            $this->db->beginTransaction();

            $distributionsCreees = 0;
            $achatsCreees = 0;

            foreach ($propositions as $p) {
                if ($p['type'] === 'nature') {
                    // Créer distribution directe
                    $stmt = $this->db->prepare("
                        INSERT INTO distribution (idBesoin, idDon, idVille, quantite, idStatusDistribution, dateDistribution)
                        VALUES (:idBesoin, :idDon, :idVille, :quantite, 2, NOW())
                    ");
                    $stmt->execute([
                        ':idBesoin' => $p['idBesoin'],
                        ':idDon' => $p['idDon'],
                        ':idVille' => $p['idVille'],
                        ':quantite' => $p['quantite'],
                    ]);
                    $distributionsCreees++;
                } elseif ($p['type'] === 'achat') {
                    // Créer achat puis distribution
                    $stmtAchat = $this->db->prepare("
                        INSERT INTO achat (id_don, date_achat, montant_total, frais_appliques)
                        VALUES (:id_don, NOW(), :montant_total, :frais)
                    ");
                    $stmtAchat->execute([
                        ':id_don' => $p['idDon'],
                        ':montant_total' => $p['total'],
                        ':frais' => $p['frais'],
                    ]);
                    $idAchat = (int)$this->db->lastInsertId();

                    // Créer distribution liée à l'achat
                    $stmtDist = $this->db->prepare("
                        INSERT INTO distribution (idBesoin, idDon, idVille, quantite, idStatusDistribution, dateDistribution, id_achat)
                        VALUES (:idBesoin, :idDon, :idVille, :quantite, 2, NOW(), :id_achat)
                    ");
                    $stmtDist->execute([
                        ':idBesoin' => $p['idBesoin'],
                        ':idDon' => $p['idDon'],
                        ':idVille' => $p['idVille'],
                        ':quantite' => $p['quantite'],
                        ':id_achat' => $idAchat,
                    ]);
                    $achatsCreees++;
                    $distributionsCreees++;
                }
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => "Simulation validée: $distributionsCreees distribution(s), $achatsCreees achat(s)",
                'distributions' => $distributionsCreees,
                'achats' => $achatsCreees,
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
