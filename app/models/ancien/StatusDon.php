<?php
namespace app\models;

class StatusDon {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getAllStatusDon() {
        $query = "SELECT * FROM statusDon";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM statusDon WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function create($nom) {
        $query = "INSERT INTO statusDon (nom) VALUES (:nom)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':nom' => $nom]);
    }

    public function update($id, $nom) {
        $query = "UPDATE statusDon SET nom = :nom WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id, ':nom' => $nom]);
    }

    public function delete($id) {
        $query = "DELETE FROM statusDon WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
