<?php
namespace app\models;

class Don {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getAllDon() {
        $query = "SELECT * FROM don";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Alias pour getAllDon (pour cohérence avec les autres modèles)
     */
    public function getAllDons() {
        return $this->getAllDon();
    }

    /**
     * Récupérer tous les dons avec détails (produit, status)
     * @return array
     */
    public function getAllDonsAvecDetails() {
        $query = "
            SELECT 
                d.id,
                d.idProduit,
                d.montant,
                d.quantite,
                d.dateDon,
                d.donateur_nom,
                p.nom AS produit_nom,
                s.nom AS status_nom,
                CASE 
                    WHEN d.idProduit IS NOT NULL THEN 'nature'
                    ELSE 'argent'
                END AS type_don
            FROM don d
            LEFT JOIN produit p ON d.idProduit = p.id
            INNER JOIN statusDon s ON d.idStatus = s.id
            ORDER BY d.dateDon DESC, d.id DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les dons disponibles avec quantités restantes calculées
     * @return array
     */
    public function getDonsDisponibles() {
        $query = "
            SELECT 
                d.id,
                d.idProduit,
                d.montant,
                d.quantite,
                d.dateDon,
                d.donateur_nom,
                p.nom AS produit_nom,
                CASE 
                    WHEN d.idProduit IS NOT NULL THEN 'nature'
                    ELSE 'argent'
                END AS type_don,
                -- Calculer la quantité déjà distribuée
                COALESCE(SUM(dist.quantite), 0) AS quantite_distribuee,
                -- Calculer la quantité restante
                CASE 
                    WHEN d.idProduit IS NOT NULL THEN d.quantite - COALESCE(SUM(dist.quantite), 0)
                    ELSE d.montant - COALESCE(SUM(dist.quantite), 0)
                END AS quantite_restante
            FROM don d
            LEFT JOIN produit p ON d.idProduit = p.id
            LEFT JOIN distribution dist ON d.id = dist.idDon
            GROUP BY d.id, d.idProduit, d.montant, d.quantite, d.dateDon, d.donateur_nom, p.nom
            HAVING quantite_restante > 0
            ORDER BY d.dateDon ASC, d.id ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer uniquement les dons financiers (argent) encore disponibles
     * @return array
     */
    public function getDonsArgentDisponibles() {
        $query = "
            SELECT d.id, d.montant, d.dateDon, d.donateur_nom, d.idStatus,
                   COALESCE(SUM(dist.montant),0) AS montant_distribue,
                   d.montant - COALESCE(SUM(dist.montant),0) AS montant_restant
            FROM don d
            LEFT JOIN distribution dist ON d.id = dist.idDon
            WHERE d.idProduit IS NULL
            GROUP BY d.id, d.montant, d.dateDon, d.donateur_nom, d.idStatus
            HAVING montant_restant > 0
            ORDER BY d.dateDon ASC, d.id ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les dons par ville (via distributions liées à la ville)
     * @param int $idVille
     * @return array
     */
    public function getDonsByVille($idVille) {
        $query = "
            SELECT DISTINCT d.*
            FROM don d
            JOIN distribution dist ON d.id = dist.idDon
            WHERE dist.idVille = :idVille
            ORDER BY d.dateDon ASC, d.id ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':idVille' => $idVille]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Mettre à jour le statut d'un don
     * @param int $idDon
     * @param int $statut
     * @return bool
     */
    public function updateStatutDon($idDon, $statut) {
        $query = "UPDATE don SET idStatus = :statut WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':statut' => $statut, ':id' => $idDon]);
    }

    /**
     * Récupérer un don par son ID
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $query = "SELECT * FROM don WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Alias pour getById
     */
    public function getDonById($id) {
        return $this->getById($id);
    }

    /**
     * Créer un nouveau don
     * @param array $data
     * @return bool
     */
    public function create($data) {
        // Utilisation des vraies colonnes de la table don
        $query = "INSERT INTO don (idProduit, montant, quantite, dateDon, idStatus, donateur_nom)
                  VALUES (:idProduit, :montant, :quantite, :dateDon, :idStatus, :donateur_nom)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':idProduit' => $data['idProduit'] ?? null,
            ':montant' => $data['montant'] ?? null,
            ':quantite' => $data['quantite'] ?? null,
            ':dateDon' => $data['dateDon'] ?? date('Y-m-d H:i:s'),
            ':idStatus' => $data['idStatus'] ?? 1,
            ':donateur_nom' => $data['donateur_nom'] ?? null
        ]);
    }

    /**
     * Mettre à jour un don
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

        $query = "UPDATE don SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Supprimer un don
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $query = "DELETE FROM don WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Récupérer les dons en argent disponibles (alias avec détails)
     * @return array
     */
    public function getDonsArgent() {
        return $this->getDonsArgentDisponibles();
    }

    /**
     * Charger les dons disponibles pour un besoin donné (filtre par ville ou global)
     * @param int|null $idVille
     * @return array
     */
    public function chargerDonsDisponibles($idVille = null) {
        if ($idVille) {
            return $this->getDonsByVille($idVille);
        }
        return $this->getDonsDisponibles();
    }

    /**
     * Sauvegarder un don dans l'historique
     * @param array $don Données du don
     * @return bool
     */
    public function saveToHistorique(array $don): bool {
        $query = "
            INSERT INTO historique_don (
                id, idProduit, montant, quantite, dateDon, idStatus, donateur_nom
            ) VALUES (
                :id, :idProduit, :montant, :quantite, :dateDon, :idStatus, :donateur_nom
            )
        ";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id' => $don['id'],
            ':idProduit' => $don['idProduit'] ?? null,
            ':montant' => $don['montant'] ?? null,
            ':quantite' => $don['quantite'] ?? null,
            ':dateDon' => $don['dateDon'],
            ':idStatus' => $don['idStatus'],
            ':donateur_nom' => $don['donateur_nom'] ?? null
        ]);
    }
}

?>