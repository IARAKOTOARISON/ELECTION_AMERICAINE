<?php
namespace app\models;

class Parametres {
    private \PDO $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM parametres");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getByKey(string $cle) {
        $stmt = $this->db->prepare("SELECT * FROM parametres WHERE cle = :cle LIMIT 1");
        $stmt->execute([':cle' => $cle]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function getValeur(string $cle, $default = null) {
        $row = $this->getByKey($cle);
        return $row ? $row['valeur'] : $default;
    }

    public function set(string $cle, $valeur, ?string $description = null) {
        // insert or update
        $existing = $this->getByKey($cle);
        if ($existing) {
            $stmt = $this->db->prepare("UPDATE parametres SET valeur = :valeur, description = :description WHERE cle = :cle");
            return $stmt->execute([':valeur' => $valeur, ':description' => $description, ':cle' => $cle]);
        }
        $stmt = $this->db->prepare("INSERT INTO parametres (cle, valeur, description) VALUES (:cle, :valeur, :description)");
        return $stmt->execute([':cle' => $cle, ':valeur' => $valeur, ':description' => $description]);
    }

    // Confort: méthodes pour frais si utilisées
    public function getFrais() {
        return $this->getValeur('frais', 0);
    }

    public function setFrais($montant) {
        return $this->set('frais', $montant, 'Frais appliqués aux achats');
    }
}
