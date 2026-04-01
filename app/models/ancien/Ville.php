<?php
namespace app\models;

class Ville {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getAllVilles() {
        $query = "SELECT * FROM ville";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer toutes les villes avec le nom de leur région
     * @return array
     */
    public function getVillesAvecRegions() {
        $query = "SELECT 
                    v.id,
                    v.nom as ville_nom,
                    v.idRegion,
                    r.nom as region_nom
                  FROM ville v
                  INNER JOIN region r ON v.idRegion = r.id
                  ORDER BY r.nom, v.nom";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer une ville par son ID
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $query = "SELECT * FROM ville WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Créer une nouvelle ville
     * @param string $nom
     * @param int $idRegion
     * @return bool
     */
    public function create($nom, $idRegion) {
        $query = "INSERT INTO ville (nom, idRegion) VALUES (:nom, :idRegion)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':nom' => $nom, ':idRegion' => $idRegion]);
    }

    /**
     * Mettre à jour une ville
     * @param int $id
     * @param string $nom
     * @param int $idRegion
     * @return bool
     */
    public function update($id, $nom, $idRegion) {
        $query = "UPDATE ville SET nom = :nom, idRegion = :idRegion WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id, ':nom' => $nom, ':idRegion' => $idRegion]);
    }

    /**
     * Supprimer une ville
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $query = "DELETE FROM ville WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
?>