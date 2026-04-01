<?php
namespace app\models;

class Region {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getAllRegions() {
        $query = "SELECT * FROM region";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer une région par son ID
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $query = "SELECT * FROM region WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Créer une nouvelle région
     * @param string $nom
     * @return bool
     */
    public function create($nom) {
        $query = "INSERT INTO region (nom) VALUES (:nom)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':nom' => $nom]);
    }

    /**
     * Mettre à jour une région
     * @param int $id
     * @param string $nom
     * @return bool
     */
    public function update($id, $nom) {
        $query = "UPDATE region SET nom = :nom WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id, ':nom' => $nom]);
    }

    /**
     * Supprimer une région
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $query = "DELETE FROM region WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
?>