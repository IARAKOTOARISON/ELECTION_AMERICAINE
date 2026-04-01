<?php
namespace app\models;

class Besoin {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getAllBesoin() {
        $query = "SELECT * FROM besoin";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer tous les besoins avec détails (ville, produit, status)
     * @return array
     */
    public function getAllBesoinsAvecDetails() {
        $query = "
            SELECT 
                b.id,
                b.quantite,
                b.dateBesoin,
                v.nom AS ville_nom,
                p.nom AS produit_nom,
                s.nom AS status_nom
            FROM besoin b
            INNER JOIN ville v ON b.idVille = v.id
            INNER JOIN produit p ON b.idProduit = p.id
            INNER JOIN statusBesoin s ON b.idStatus = s.id
            ORDER BY b.dateBesoin DESC, b.id DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les besoins non satisfaits avec quantités restantes calculées
     * @return array
     */
    public function getBesoinsNonSatisfaits() {
        $query = "
            SELECT 
                b.id,
                b.idVille,
                b.idProduit,
                b.quantite,
                b.dateBesoin,
                v.nom AS ville_nom,
                p.nom AS produit_nom,
                -- Calculer la quantité déjà distribuée
                COALESCE(SUM(dist.quantite), 0) AS quantite_distribuee,
                -- Calculer la quantité restante
                b.quantite - COALESCE(SUM(dist.quantite), 0) AS quantite_restante
            FROM besoin b
            INNER JOIN ville v ON b.idVille = v.id
            INNER JOIN produit p ON b.idProduit = p.id
            LEFT JOIN distribution dist ON b.id = dist.idBesoin
            GROUP BY b.id, b.idVille, b.idProduit, b.quantite, b.dateBesoin, v.nom, p.nom
            HAVING quantite_restante > 0
            ORDER BY b.dateBesoin ASC, b.id ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les besoins restants (même logique que non satisfaits)
     * @return array
     */
    public function getBesoinsRestants() {
        return $this->getBesoinsNonSatisfaits();
    }

    /**
     * Récupérer un besoin restant par ID (avec quantité restante calculée)
     * Alias: getBesoinRestant — Liste besoins non satisfaits (singulier)
     * @param int $id
     * @return array|null
     */
    public function getBesoinRestant(int $id): ?array {
        $query = "
            SELECT 
                b.id,
                b.idVille,
                b.idProduit,
                b.quantite,
                b.dateBesoin,
                v.nom AS ville_nom,
                p.nom AS produit_nom,
                p.prixUnitaire,
                COALESCE(SUM(dist.quantite), 0) AS quantite_distribuee,
                b.quantite - COALESCE(SUM(dist.quantite), 0) AS quantite_restante
            FROM besoin b
            INNER JOIN ville v ON b.idVille = v.id
            INNER JOIN produit p ON b.idProduit = p.id
            LEFT JOIN distribution dist ON b.id = dist.idBesoin
            WHERE b.id = :id
            GROUP BY b.id, b.idVille, b.idProduit, b.quantite, b.dateBesoin, v.nom, p.nom, p.prixUnitaire
            HAVING quantite_restante > 0
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Récupérer les besoins d'une ville donnée
     * @param int $idVille
     * @return array
     */
    public function getBesoinsByVille($idVille) {
        $query = "
            SELECT 
                b.id,
                b.idVille,
                b.idProduit,
                b.quantite,
                b.dateBesoin,
                v.nom AS ville_nom,
                p.nom AS produit_nom,
                COALESCE(SUM(dist.quantite), 0) AS quantite_distribuee,
                b.quantite - COALESCE(SUM(dist.quantite), 0) AS quantite_restante
            FROM besoin b
            INNER JOIN ville v ON b.idVille = v.id
            INNER JOIN produit p ON b.idProduit = p.id
            LEFT JOIN distribution dist ON b.id = dist.idBesoin
            WHERE b.idVille = :idVille
            GROUP BY b.id, b.idVille, b.idProduit, b.quantite, b.dateBesoin, v.nom, p.nom
            ORDER BY b.dateBesoin ASC, b.id ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':idVille' => $idVille]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un besoin par son ID
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $query = "SELECT * FROM besoin WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Alias pour getById
     */
    public function getBesoinById($id) {
        return $this->getById($id);
    }

    /**
     * Créer un nouveau besoin
     * @param array $data
     * @return bool
     */
    public function create($data) {
        $query = "INSERT INTO besoin (idVille, idProduit, quantite, idStatus, dateBesoin)
                  VALUES (:idVille, :idProduit, :quantite, :idStatus, :dateBesoin)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($data);
    }

    /**
     * Mettre à jour un besoin
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $updates = [];
        $params = [':id' => $id];
        
        foreach ($data as $key => $value) {
            $updates[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        if (empty($updates)) {
            return false;
        }

        $query = "UPDATE besoin SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Supprimer un besoin
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $query = "DELETE FROM besoin WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Calculer le coût total avec frais pour un besoin
     * Formule: prix × qté × (1 + frais/100)
     * @param int $idBesoin ID du besoin
     * @return array Détails du calcul [cout_base, frais, cout_total, taux_frais]
     */
    public function calculerCoutAvecFrais($idBesoin): array {
        // Récupérer le besoin avec prix unitaire
        $besoin = $this->getBesoinRestant((int)$idBesoin);
        if (!$besoin) {
            // Si c'est un montant direct (ancienne signature)
            if (is_numeric($idBesoin)) {
                $montant = (float)$idBesoin;
                $tauxFrais = $this->getTauxFrais();
                return [
                    'cout_base' => $montant,
                    'taux_frais' => $tauxFrais,
                    'frais' => $montant * ($tauxFrais / 100),
                    'cout_total' => $montant * (1 + ($tauxFrais / 100))
                ];
            }
            return ['cout_base' => 0, 'taux_frais' => 0, 'frais' => 0, 'cout_total' => 0];
        }

        $prix = (float)($besoin['prixUnitaire'] ?? 0);
        $quantite = (float)($besoin['quantite_restante'] ?? $besoin['quantite'] ?? 0);
        $tauxFrais = $this->getTauxFrais();

        // Formule: prix × qté × (1 + frais/100)
        $coutBase = $prix * $quantite;
        $frais = $coutBase * ($tauxFrais / 100);
        $coutTotal = $coutBase * (1 + ($tauxFrais / 100));

        return [
            'cout_base' => $coutBase,
            'taux_frais' => $tauxFrais,
            'frais' => $frais,
            'cout_total' => $coutTotal,
            'prix_unitaire' => $prix,
            'quantite' => $quantite
        ];
    }

    /**
     * Récupérer le taux de frais depuis la table parametres
     * @return float Taux de frais en pourcentage (défaut: 10)
     */
    private function getTauxFrais(): float {
        try {
            $stmt = $this->db->prepare("SELECT valeur FROM parametres WHERE cle = 'frais_achat' LIMIT 1");
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? (float)$row['valeur'] : 10;
        } catch (\PDOException $e) {
            return 10; // 10% par défaut
        }
    }

    /**
     * Vérifier si un besoin a déjà été acheté OU si un don en nature existe encore
     * Conformément au sujet : "Il y a un message d'erreur si l'achat existe encore dans les dons restants"
     * @param int $idBesoin
     * @return array [deja_achete => bool, don_nature_disponible => bool, details => array|null, message_erreur => string|null]
     */
    public function verifierBesoin(int $idBesoin): array {
        try {
            // 1. Vérifier si une distribution avec achat existe pour ce besoin
            $stmt = $this->db->prepare("
                SELECT 
                    d.id AS id_distribution,
                    d.quantite,
                    d.dateDistribution,
                    a.id AS id_achat,
                    a.montant_total,
                    a.frais_appliques,
                    a.date_achat
                FROM distribution d
                LEFT JOIN achat a ON d.id_achat = a.id
                WHERE d.idBesoin = :idBesoin AND d.id_achat IS NOT NULL
                LIMIT 1
            ");
            $stmt->execute([':idBesoin' => $idBesoin]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row) {
                return [
                    'deja_achete' => true,
                    'don_nature_disponible' => false,
                    'details' => $row,
                    'message_erreur' => 'Ce besoin a déjà été acheté'
                ];
            }

            // 2. Vérifier si un don en nature existe pour le même produit (conformément au sujet)
            $besoin = $this->getById($idBesoin);
            if ($besoin && isset($besoin['idProduit'])) {
                $stmtDonNature = $this->db->prepare("
                    SELECT 
                        dn.id,
                        dn.quantite,
                        p.nom AS produit_nom,
                        COALESCE(SUM(dist.quantite), 0) AS quantite_distribuee,
                        dn.quantite - COALESCE(SUM(dist.quantite), 0) AS quantite_restante
                    FROM don dn
                    INNER JOIN produit p ON dn.idProduit = p.id
                    LEFT JOIN distribution dist ON dn.id = dist.idDon
                    WHERE dn.idProduit = :idProduit AND dn.idStatusDon != 3
                    GROUP BY dn.id, dn.quantite, p.nom
                    HAVING quantite_restante > 0
                    LIMIT 1
                ");
                $stmtDonNature->execute([':idProduit' => $besoin['idProduit']]);
                $donNature = $stmtDonNature->fetch(\PDO::FETCH_ASSOC);

                if ($donNature) {
                    return [
                        'deja_achete' => false,
                        'don_nature_disponible' => true,
                        'details' => $donNature,
                        'message_erreur' => 'Un don en nature du même produit (' . $donNature['produit_nom'] . ') est encore disponible (' . $donNature['quantite_restante'] . ' unités). Utilisez d\'abord les dons en nature avant de faire un achat.'
                    ];
                }
            }

            return [
                'deja_achete' => false,
                'don_nature_disponible' => false,
                'details' => null,
                'message_erreur' => null
            ];
        } catch (\PDOException $e) {
            return [
                'deja_achete' => false,
                'don_nature_disponible' => false,
                'details' => null,
                'message_erreur' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Acheter un besoin — créer achat + distribution
     * @param int $idBesoin
     * @param int|null $idDon ID du don financier (auto-sélection si null)
     * @return array Résultat [success, message, id_achat]
     */
    public function acheterBesoin(int $idBesoin, ?int $idDon = null): array {
        try {
            // Vérifier si déjà acheté OU si un don en nature existe (conformément au sujet)
            $verif = $this->verifierBesoin($idBesoin);
            if ($verif['deja_achete']) {
                return [
                    'success' => false,
                    'message' => $verif['message_erreur'] ?? 'Ce besoin a déjà été acheté',
                    'details' => $verif['details']
                ];
            }
            // Message d'erreur si l'achat existe encore dans les dons restants
            if ($verif['don_nature_disponible']) {
                return [
                    'success' => false,
                    'message' => $verif['message_erreur'],
                    'details' => $verif['details']
                ];
            }

            $besoin = $this->getById($idBesoin);
            if (!$besoin) {
                return ['success' => false, 'message' => 'Besoin introuvable'];
            }

            // Calculer le coût avec frais
            $cout = $this->calculerCoutAvecFrais($idBesoin);
            $montantTotal = $cout['cout_total'];

            // Si pas de don spécifié, trouver un don disponible
            if (!$idDon) {
                $donModel = new Don($this->db);
                $donsArgent = $donModel->getDonsArgentDisponibles();
                foreach ($donsArgent as $don) {
                    if ($this->verifierDonDisponible($don['id'], $montantTotal)) {
                        $idDon = $don['id'];
                        break;
                    }
                }
            }

            if (!$idDon) {
                return ['success' => false, 'message' => 'Aucun don financier disponible'];
            }

            // Vérifier que le don peut couvrir le montant
            if (!$this->verifierDonDisponible($idDon, $montantTotal)) {
                return ['success' => false, 'message' => 'Don insuffisant pour couvrir ce besoin'];
            }

            $this->db->beginTransaction();

            // Créer l'achat
            $stmtAchat = $this->db->prepare("
                INSERT INTO achat (id_don, date_achat, montant_total, frais_appliques)
                VALUES (:id_don, NOW(), :montant_total, :frais)
            ");
            $stmtAchat->execute([
                ':id_don' => $idDon,
                ':montant_total' => $montantTotal,
                ':frais' => $cout['frais']
            ]);
            $idAchat = (int)$this->db->lastInsertId();

            // Créer la distribution liée à l'achat
            $stmtDist = $this->db->prepare("
                INSERT INTO distribution (idBesoin, idDon, idVille, quantite, idStatusDistribution, dateDistribution, id_achat)
                VALUES (:idBesoin, :idDon, :idVille, :quantite, 2, NOW(), :id_achat)
            ");
            $stmtDist->execute([
                ':idBesoin' => $idBesoin,
                ':idDon' => $idDon,
                ':idVille' => $besoin['idVille'],
                ':quantite' => $besoin['quantite'],
                ':id_achat' => $idAchat
            ]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Achat effectué avec succès',
                'id_achat' => $idAchat,
                'cout' => $cout
            ];

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Vérifier si un don financier peut couvrir un montant donné
     * @param int $idDon
     * @param float $montant
     * @return bool
     */
    public function verifierDonDisponible($idDon, $montant) {
        try {
            $stmt = $this->db->prepare("
                SELECT d.montant, COALESCE(SUM(dist.quantite), 0) AS utilise
                FROM don d
                LEFT JOIN distribution dist ON d.id = dist.idDon
                WHERE d.id = :idDon AND d.idProduit IS NULL
                GROUP BY d.id, d.montant
            ");
            $stmt->execute([':idDon' => $idDon]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) return false;
            $restant = $row['montant'] - $row['utilise'];
            return ($restant >= $montant);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Acheter automatiquement des besoins sélectionnés en utilisant un don financier
     * @param array $besoinIds Liste d'IDs de besoins
     * @return array Résultat avec succès/échec et détails
     */
    public function acheterBesoinsAuto($besoinIds) {
        if (empty($besoinIds)) {
            return ['success' => false, 'message' => 'Aucun besoin sélectionné'];
        }

        try {
            $this->db->beginTransaction();

            // Récupérer dons financiers disponibles
            $donModel = new Don($this->db);
            $donsArgent = $donModel->getDonsArgentDisponibles();
            if (empty($donsArgent)) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Aucun don financier disponible'];
            }

            $achatService = new AchatAutoService($this->db);
            $achatsCreated = [];

            foreach ($besoinIds as $idBesoin) {
                $besoin = $this->getById($idBesoin);
                if (!$besoin) continue;

                $cout = $achatService->calculerCoutTotal($besoin);
                $coutAvecFrais = $this->calculerCoutAvecFrais($cout);

                // Trouver un don qui peut couvrir
                $donUtilise = null;
                foreach ($donsArgent as $don) {
                    if ($this->verifierDonDisponible($don['id'], $coutAvecFrais)) {
                        $donUtilise = $don;
                        break;
                    }
                }

                if (!$donUtilise) continue;

                // Créer l'achat
                $idAchat = $achatService->acheterBesoin($besoin, (int)$donUtilise['id']);
                if ($idAchat) {
                    $achatsCreated[] = $idAchat;
                    // Créer distribution
                    $mapping = [[
                        'idBesoin' => $idBesoin,
                        'idDon' => $donUtilise['id'],
                        'idVille' => $besoin['idVille'] ?? 1,
                        'quantite' => $besoin['quantite'] ?? 0,
                        'idStatusDistribution' => 2,
                        'dateDistribution' => date('Y-m-d H:i:s')
                    ]];
                    $achatService->creerDistributionDepuisAchat($idAchat, $mapping);
                }
            }

            $this->db->commit();
            return [
                'success' => true,
                'message' => count($achatsCreated) . ' achat(s) créé(s)',
                'achats' => $achatsCreated
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Acheter manuellement un besoin (avec sélection explicite du don)
     * @param int $idBesoin
     * @param int $idDon
     * @return array Résultat avec succès/échec
     */
    public function acheterBesoinManuel($idBesoin, $idDon) {
        try {
            $this->db->beginTransaction();

            $besoin = $this->getById($idBesoin);
            if (!$besoin) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Besoin introuvable'];
            }

            $achatService = new AchatAutoService($this->db);
            $cout = $achatService->calculerCoutTotal($besoin);
            $coutAvecFrais = $this->calculerCoutAvecFrais($cout);

            if (!$this->verifierDonDisponible($idDon, $coutAvecFrais)) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Don insuffisant'];
            }

            $idAchat = $achatService->acheterBesoin($besoin, $idDon);
            if (!$idAchat) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Échec création achat'];
            }

            // Créer distribution
            $mapping = [[
                'idBesoin' => $idBesoin,
                'idDon' => $idDon,
                'idVille' => $besoin['idVille'] ?? 1,
                'quantite' => $besoin['quantite'] ?? 0,
                'idStatusDistribution' => 2,
                'dateDistribution' => date('Y-m-d H:i:s')
            ]];
            $achatService->creerDistributionDepuisAchat($idAchat, $mapping);

            $this->db->commit();
            return ['success' => true, 'message' => 'Achat créé', 'idAchat' => $idAchat];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Sauvegarder un besoin dans l'historique
     * @param array $besoin Données du besoin
     * @return bool
     */
    public function saveToHistorique(array $besoin): bool {
        $query = "
            INSERT INTO historique_besoin (
                id, idVille, idProduit, quantite, idStatus, dateBesoin
            ) VALUES (
                :id, :idVille, :idProduit, :quantite, :idStatus, :dateBesoin
            )
        ";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id' => $besoin['id'],
            ':idVille' => $besoin['idVille'],
            ':idProduit' => $besoin['idProduit'],
            ':quantite' => $besoin['quantite'],
            ':idStatus' => $besoin['idStatus'],
            ':dateBesoin' => $besoin['dateBesoin']
        ]);
    }
}
?>