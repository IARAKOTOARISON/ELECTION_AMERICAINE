<?php
namespace app\models;

class Reinitialize {
    private \PDO $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function reinitialize(): void {
        $this->rollbackTable('besoin', 'historique_besoin');
        $this->rollbackTable('don', 'historique_don');
        $this->rollbackTable('distribution', 'historique_distribution');
    }


    private function rollbackTable(string $table, string $history): void {
        // Récupérer les dernières versions historiques
        $rows = $this->getLastHistory($history);

        foreach ($rows as $row) {
            // On prépare dynamiquement les colonnes à mettre à jour (sauf les colonnes "techniques")
            $columns = array_filter(array_keys($row), fn($c) => !in_array($c, ['history_id', 'changed_at']));
            $set = implode(', ', array_map(fn($c) => "$c = :$c", $columns));

            $update = $this->db->prepare("UPDATE $table SET $set WHERE id = :id");
            $params = [];
            foreach ($columns as $col) {
                $params[":$col"] = $row[$col];
            }
            $params[':id'] = $row['id'];
            $update->execute($params);
        }
    }

    private function getLastHistory(string $history): array {
        $stmt = $this->db->prepare("
            SELECT h.*
            FROM $history h
            INNER JOIN (
                SELECT id, MAX(changed_at) AS max_time
                FROM $history
                GROUP BY id
            ) AS last ON h.id = last.id AND h.changed_at = last.max_time
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
