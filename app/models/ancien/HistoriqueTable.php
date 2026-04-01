<?php
namespace app\models;

class HistoriqueTable {
    private \PDO $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function copierDansHistorique(string $table, string $history): int {
        // Récupérer toutes les colonnes de la table principale
        $stmt = $this->db->query("SHOW COLUMNS FROM `$table`");
        $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // On enlève les colonnes auto-incrément et timestamp de l'historique
        $columns = array_filter($columns, fn($col) => $col !== 'id');

        // Construire la liste des colonnes à copier dans la table historique
        $columnsList = implode(', ', $columns);

        // Construire la requête d'insertion
        $sql = "INSERT INTO `$history` ($columnsList, changed_at) SELECT $columnsList, NOW() FROM `$table`";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->rowCount();
    }
}
?>
