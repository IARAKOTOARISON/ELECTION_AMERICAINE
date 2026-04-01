<?php
namespace app\models;

class Distribution {
    private \PDO $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    /** Trier un tableau par date */
    private function sortByDate(array $array, string $key, string $order = 'ASC'): array {
        usort($array, function($a, $b) use ($key, $order) {
            $timeA = strtotime($a[$key]);
            $timeB = strtotime($b[$key]);
            return ($order === 'ASC') ? ($timeA - $timeB) : ($timeB - $timeA);
        });
        return $array;
    }

    /** Filtrer besoins par ville */
    private function filterBesoinsByVille(array $besoins, int $villeId): array {
        return array_filter($besoins, fn($b) => $b['idVille'] == $villeId);
    }

    /** Déterminer type de besoin (quantité ou montant) */
    private function getTypeBesoin(array $besoin): string {
        return isset($besoin['quantite']) ? 'quantite' : 'montant';
    }

    /** Distribuer un don à un besoin */
    private function distribuerBesoinDon(array &$besoin, array &$don, string $typeBesoin): ?array {
        $besoinRestant = $besoin[$typeBesoin] ?? 0;
        $donRestant = $don[$typeBesoin] ?? 0;

        if ($besoinRestant <= 0 || $donRestant <= 0) return null;

        $attribue = min($besoinRestant, $donRestant);

        // Mettre à jour besoin et don
        $besoin[$typeBesoin] -= $attribue;
        $don[$typeBesoin] -= $attribue;

        // Statuts
        $besoin['idStatus'] = ($besoin[$typeBesoin] <= 0) ? 3 : 2; // Satisfait / Partiellement
        $don['idStatus'] = ($don[$typeBesoin] <= 0) ? 3 : 2; // Distribué / Alloué partiellement

        return [
            'idVille' => $besoin['idVille'],
            'idBesoin' => $besoin['id'],
            'idDon' => $don['id'],
            $typeBesoin => $attribue,
            'dateDistribution' => date('Y-m-d H:i:s'),
            'idStatusDistribution' => 2 // Effectué
        ];
    }

    /** Simuler la distribution pour toutes les villes */
    public function distribuer(array $allVille, array $allDon, array $allBesoin): array {
        $distributions = [];

        $allBesoin = $this->sortByDate($allBesoin, 'dateBesoin');
        $allDon = $this->sortByDate($allDon, 'dateDon');

        foreach ($allVille as &$ville) {
            $villeId = $ville['id'];
            $besoinsVille = $this->filterBesoinsByVille($allBesoin, $villeId);

            foreach ($besoinsVille as &$besoin) {
                $typeBesoin = $this->getTypeBesoin($besoin);

                foreach ($allDon as &$don) {
                    if (($don[$typeBesoin] ?? 0) <= 0 || $don['idStatus'] != 1) continue;

                    while (($besoin[$typeBesoin] ?? 0) > 0 && ($don[$typeBesoin] ?? 0) > 0) {
                        $result = $this->distribuerBesoinDon($besoin, $don, $typeBesoin);
                        if ($result) $distributions[] = $result;
                    }
                }

                // Calcul du reste pour le tableau de bord
                $besoin['reste'] = $besoin[$typeBesoin] ?? 0;
                $besoin['progression'] = $besoin['quantite'] > 0
                    ? round((($besoin['quantite'] - $besoin['reste']) / $besoin['quantite']) * 100)
                    : 0;
                // Statut visuel pour la vue
                $besoin['statutVisuel'] = match(true) {
                    $besoin['progression'] >= 100 => 'Complet',
                    $besoin['progression'] >= 50 => 'En cours',
                    default => 'Urgent',
                };
            }
        }

        return $allBesoin;
    }

    /**
     * Créer une distribution en base de données
     * @param array $data
     * @return bool|int Retourne l'ID de la distribution créée ou false
     */
    public function create($data) {
        $query = "INSERT INTO distribution (idBesoin, idDon, idVille, quantite, idStatusDistribution, dateDistribution)
                  VALUES (:idBesoin, :idDon, :idVille, :quantite, :idStatusDistribution, :dateDistribution)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute($data);
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Récupérer toutes les distributions avec détails
     * @return array
     */
    public function getAllDistributionsAvecDetails() {
        $query = "
            SELECT 
                d.id,
                d.idBesoin,
                d.idDon,
                d.quantite,
                d.dateDistribution,
                v.nom AS ville_nom,
                p.nom AS produit_nom,
                don.donateur_nom,
                don.dateDon,
                b.quantite AS besoin_quantite,
                b.dateBesoin,
                s.nom AS status_nom
            FROM distribution d
            INNER JOIN besoin b ON d.idBesoin = b.id
            INNER JOIN ville v ON b.idVille = v.id
            INNER JOIN produit p ON b.idProduit = p.id
            INNER JOIN don don ON d.idDon = don.id
            INNER JOIN statusDistribution s ON d.idStatusDistribution = s.id
            ORDER BY d.dateDistribution DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Supprimer toutes les distributions (pour réinitialiser)
     * @return bool
     */
    public function deleteAll() {
        $query = "DELETE FROM distribution";
        $stmt = $this->db->prepare($query);
        return $stmt->execute();
    }

    /**
     * Sauvegarder une distribution dans l'historique
     * @param array $distribution Données de la distribution
     * @return bool
     */
    public function saveToHistorique(array $distribution): bool {
        $query = "
            INSERT INTO historique_distribution (
                id, idBesoin, idDon, idVille, quantite, montant, 
                dateDistribution, idStatusDistribution
            ) VALUES (
                :id, :idBesoin, :idDon, :idVille, :quantite, :montant,
                :dateDistribution, :idStatusDistribution
            )
        ";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id' => $distribution['id'] ?? null,
            ':idBesoin' => $distribution['idBesoin'],
            ':idDon' => $distribution['idDon'],
            ':idVille' => $distribution['idVille'],
            ':quantite' => $distribution['quantite'] ?? null,
            ':montant' => $distribution['montant'] ?? null,
            ':dateDistribution' => $distribution['dateDistribution'] ?? date('Y-m-d H:i:s'),
            ':idStatusDistribution' => $distribution['idStatusDistribution'] ?? 1
        ]);
    }
}

