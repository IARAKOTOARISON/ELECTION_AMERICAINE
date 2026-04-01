<?php
namespace app\models;

class AchatDetails {
    private \PDO $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function create(array $data) {
        $query = "INSERT INTO achat_details (id_achat, id_produit, quantite, prix_unitaire) VALUES (:id_achat, :id_produit, :quantite, :prix_unitaire)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id_achat' => $data['id_achat'],
            ':id_produit' => $data['id_produit'],
            ':quantite' => $data['quantite'],
            ':prix_unitaire' => $data['prix_unitaire'],
        ]);
    }

    public function getByAchat($idAchat) {
        $stmt = $this->db->prepare("SELECT * FROM achat_details WHERE id_achat = :idAchat");
        $stmt->execute([':idAchat' => $idAchat]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function deleteByAchat($idAchat) {
        $stmt = $this->db->prepare("DELETE FROM achat_details WHERE id_achat = :idAchat");
        return $stmt->execute([':idAchat' => $idAchat]);
    }
}
