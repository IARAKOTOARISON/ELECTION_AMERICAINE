<?php
namespace app\models;

class Produit {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getAllProduits() {
        $query = "SELECT * FROM produit";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM produit WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function create($data) {
        $query = "INSERT INTO produit (idCategorie, nom, prixUnitaire) VALUES (:idCategorie, :nom, :prixUnitaire)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':idCategorie' => $data['idCategorie'] ?? null,
            ':nom' => $data['nom'] ?? null,
            ':prixUnitaire' => $data['prixUnitaire'] ?? 0,
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

        $query = "UPDATE produit SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $query = "DELETE FROM produit WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
