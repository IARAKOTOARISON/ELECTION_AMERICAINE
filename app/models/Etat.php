<?php
namespace app\models;

class Etat {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getAllEtats() {
        $query = "SELECT * FROM etats";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM etats WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function create($data) {
        $query = "INSERT INTO etats (nom, nbGrandsElecteurs) VALUES (:nom, :nbGrandsElecteurs)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':nom' => $data['nom'] ?? null,
            ':nbGrandsElecteurs' => $data['nbGrandsElecteurs'] ?? 0,
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

        $query = "UPDATE etats SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $query = "DELETE FROM etats WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
