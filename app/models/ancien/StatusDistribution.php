<?php
namespace app\models;

class StatusDistribution {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getAllStatusDistribution() {
        $query = "SELECT * FROM statusDistribution";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM statusDistribution WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function create($nom) {
        $query = "INSERT INTO statusDistribution (nom) VALUES (:nom)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':nom' => $nom]);
    }

    public function update($id, $nom) {
        $query = "UPDATE statusDistribution SET nom = :nom WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id, ':nom' => $nom]);
    }

    public function delete($id) {
        $query = "DELETE FROM statusDistribution WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
