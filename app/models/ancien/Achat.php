<?php
namespace app\models;

class Achat {
    private \PDO $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getAllAchats() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM achat ORDER BY date_achat DESC");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Table missing or other DB error — return empty list to keep app running
            return [];
        }
    }

    public function getAchatsByDon($idDon) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM achat WHERE id_don = :idDon ORDER BY date_achat DESC");
            $stmt->execute([':idDon' => $idDon]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Récupérer achats liés à une ville via distributions
     */
    public function getAchatsByVille($idVille) {
        $query = "
            SELECT a.*
            FROM achat a
            JOIN distribution d ON d.id_achat = a.id
            WHERE d.idVille = :idVille
            ORDER BY a.date_achat DESC
        ";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([':idVille' => $idVille]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function createAchat(array $data) {
        try {
            $query = "INSERT INTO achat (id_don, date_achat, montant_total, frais_appliques) VALUES (:id_don, :date_achat, :montant_total, :frais_appliques)";
            $stmt = $this->db->prepare($query);
            $ok = $stmt->execute([
                ':id_don' => $data['id_don'] ?? null,
                ':date_achat' => $data['date_achat'] ?? date('Y-m-d H:i:s'),
                ':montant_total' => $data['montant_total'] ?? 0,
                ':frais_appliques' => $data['frais_appliques'] ?? 0,
            ]);
            return $ok ? (int)$this->db->lastInsertId() : false;
        } catch (\PDOException $e) {
            // If table doesn't exist or insert fails, return false
            return false;
        }
    }

    /**
     * Acheter automatiquement des besoins sélectionnés
     * @param array $besoinIds
     * @return array Résultat avec succès/échec et IDs achats créés
     */
    public function acheterBesoinsSelectionnes($besoinIds) {
        if (empty($besoinIds)) {
            return ['success' => false, 'message' => 'Aucun besoin sélectionné'];
        }

        try {
            $this->db->beginTransaction();
            $besoinModel = new Besoin($this->db);
            $result = $besoinModel->acheterBesoinsAuto($besoinIds);
            
            if ($result['success']) {
                $this->db->commit();
            } else {
                $this->db->rollBack();
            }
            return $result;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Soumettre un achat (création manuelle avec validation)
     * @param array $data Données de l'achat
     * @return array Résultat
     */
    public function soumettreAchat($data) {
        try {
            $this->db->beginTransaction();
            $idAchat = $this->createAchat($data);
            if (!$idAchat) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Échec création achat'];
            }
            $this->db->commit();
            return ['success' => true, 'idAchat' => $idAchat];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Lancer un achat automatique selon priorité (besoins les plus anciens)
     * @return array Résultat avec achats créés
     */
    public function lancerAchatAuto() {
        try {
            $service = new AchatAutoService($this->db);
            $besoins = $service->getBesoinsPrioritaires(10);
            $achatsCreated = [];

            $this->db->beginTransaction();
            foreach ($besoins as $b) {
                $dons = $service->verifierDonsDisponibles($b);
                if (empty($dons)) continue;
                $don = $dons[0];
                $idAchat = $service->acheterBesoin($b, (int)($don['id'] ?? $don['ID'] ?? 0));
                if ($idAchat) {
                    $achatsCreated[] = $idAchat;
                    // Créer distribution
                    $mapping = [[
                        'idBesoin' => $b['id'],
                        'idDon' => $don['id'] ?? $don['ID'],
                        'idVille' => $b['idVille'] ?? 1,
                        'quantite' => $b['quantite'] ?? 0,
                        'idStatusDistribution' => 2,
                        'dateDistribution' => date('Y-m-d H:i:s')
                    ]];
                    $service->creerDistributionDepuisAchat($idAchat, $mapping);
                }
            }
            $this->db->commit();
            return ['success' => true, 'achats' => $achatsCreated, 'count' => count($achatsCreated)];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

