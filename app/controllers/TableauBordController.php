<?php
namespace app\controllers;

use app\models\Ville;
use app\models\Besoin;
use app\models\Don;
use app\models\Distribution;
use flight\Engine;

class TableauBordController extends BaseController {

    /** Préparer les données pour le tableau de bord */
    public function getAllAboutVille() {
        $besoinModel = new Besoin($this->db);
        $donModel = new Don($this->db);
        $villeModel = new Ville($this->db);
        
        // Récupérer tous les besoins avec détails
        $besoins = $besoinModel->getAllBesoinsAvecDetails();
        
        // Calculer les statistiques pour chaque besoin
        $besoinsAvecStats = [];
        foreach ($besoins as $besoin) {
            // Calculer la quantité distribuée pour ce besoin
            $query = "SELECT COALESCE(SUM(quantite), 0) as quantite_distribuee 
                      FROM distribution 
                      WHERE idBesoin = :idBesoin";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':idBesoin' => $besoin['id']]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $quantite_distribuee = $result['quantite_distribuee'];
            $quantite_restante = $besoin['quantite'] - $quantite_distribuee;
            $progression = ($besoin['quantite'] > 0) 
                ? round(($quantite_distribuee / $besoin['quantite']) * 100) 
                : 0;
            
            // Déterminer le statut
            $statut = match(true) {
                $progression >= 100 => 'Complet',
                $progression >= 50 => 'En cours',
                default => 'Urgent',
            };
            
            $besoinsAvecStats[] = [
                'id' => $besoin['id'],
                'ville' => $besoin['ville_nom'],
                'produit' => $besoin['produit_nom'],
                'quantite' => $besoin['quantite'],
                'quantite_distribuee' => $quantite_distribuee,
                'reste' => $quantite_restante,
                'progression' => $progression,
                'statut' => $statut,
                'status_nom' => $besoin['status_nom']
            ];
        }
        
        // Calculer les statistiques globales
        $stats = [
            'total_villes' => count(array_unique(array_column($besoinsAvecStats, 'ville'))),
            'total_besoins' => count($besoinsAvecStats),
            'total_dons' => count($donModel->getAllDons()),
            'total_distributions' => $this->getNombreDistributions(),
            'quantite_totale_demandee' => array_sum(array_column($besoinsAvecStats, 'quantite')),
            'quantite_totale_distribuee' => array_sum(array_column($besoinsAvecStats, 'quantite_distribuee')),
            'quantite_totale_restante' => array_sum(array_column($besoinsAvecStats, 'reste')),
            'besoins_complets' => count(array_filter($besoinsAvecStats, fn($b) => $b['statut'] === 'Complet')),
            'besoins_en_cours' => count(array_filter($besoinsAvecStats, fn($b) => $b['statut'] === 'En cours')),
            'besoins_urgents' => count(array_filter($besoinsAvecStats, fn($b) => $b['statut'] === 'Urgent')),
        ];
        
        // Calculer le taux de satisfaction global
        $stats['taux_satisfaction'] = ($stats['quantite_totale_demandee'] > 0)
            ? round(($stats['quantite_totale_distribuee'] / $stats['quantite_totale_demandee']) * 100)
            : 0;
        
        // Rendu vers la vue
        $this->app->render('tableauBord', [
            'aboutVille' => $besoinsAvecStats,
            'stats' => $stats,
            'baseUrl' => $this->getBaseUrl(),
            'nonce' => $this->getNonce()
        ]);
    }
    
    /**
     * Récupérer le nombre total de distributions
     */
    private function getNombreDistributions() {
        $query = "SELECT COUNT(*) as total FROM distribution";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}

