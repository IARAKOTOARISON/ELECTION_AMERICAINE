<?php
namespace app\models;

class Candidat {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getAllCandidats() {
        $query = "SELECT * FROM candidats";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM candidats WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function create($data) {
        $query = "INSERT INTO candidats (nom) VALUES (:nom)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':nom' => $data['nom'] ?? null,
        ]);
    }

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

        $query = "UPDATE candidats SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $query = "DELETE FROM candidats WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
