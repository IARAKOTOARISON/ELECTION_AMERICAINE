<?php
namespace app\models;

class TypeBesoin {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getAllTypesBesoin() {
        $query = "SELECT * FROM type_besoin";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un type de besoin par son ID
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $query = "SELECT * FROM type_besoin WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Créer un nouveau type de besoin
     * @param string $nom
     * @param string $description
     * @return bool
     */
    public function create($nom, $description = null) {
        $query = "INSERT INTO type_besoin (nom, description) VALUES (:nom, :description)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':nom' => $nom, ':description' => $description]);
    }

    /**
     * Mettre à jour un type de besoin
     * @param int $id
     * @param string $nom
     * @param string $description
     * @return bool
     */
    public function update($id, $nom, $description = null) {
        $query = "UPDATE type_besoin SET nom = :nom, description = :description WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id, ':nom' => $nom, ':description' => $description]);
    }

    /**
     * Supprimer un type de besoin
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $query = "DELETE FROM type_besoin WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
?>