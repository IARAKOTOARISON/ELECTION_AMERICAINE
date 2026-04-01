<?php
namespace app\models;

class Vote {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getAllVotes() {
        $query = "SELECT * FROM votes";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM votes WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function create($data) {
        $query = "INSERT INTO votes (id_etat, id_candidat, nb_voix) VALUES (:id_etat, :id_candidat, :nb_voix)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id_etat' => $data['id_etat'] ?? null,
            ':id_candidat' => $data['id_candidat'] ?? null,
            ':nb_voix' => $data['nb_voix'] ?? 0,
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

        $query = "UPDATE votes SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $query = "DELETE FROM votes WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
