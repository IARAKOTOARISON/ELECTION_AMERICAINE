<?php
namespace app\controllers;

use app\models\Ville;
use app\models\Region;
use flight\Engine;

class VilleController extends BaseController {

    /**
     * Afficher la liste des villes avec leurs régions
     */
    public function afficherListe() {
        $villeModel = new Ville($this->db);
        $regionModel = new Region($this->db);
        
        // Récupérer toutes les villes avec leurs régions
        $villes = $villeModel->getVillesAvecRegions();
        
        // Récupérer toutes les régions pour les statistiques
        $regions = $regionModel->getAllRegions();
        
        // Calculer les statistiques
        $stats = [
            'total_villes' => count($villes),
            'total_regions' => count($regions)
        ];
        
        // Compter les villes par région
        $villesParRegion = [];
        foreach ($villes as $ville) {
            $regionNom = $ville['region_nom'];
            if (!isset($villesParRegion[$regionNom])) {
                $villesParRegion[$regionNom] = 0;
            }
            $villesParRegion[$regionNom]++;
        }
        $stats['villes_par_region'] = $villesParRegion;
        
        // Récupérer les messages flash
        $success = $_SESSION['success_message'] ?? null;
        $error = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);
        
        // Afficher la vue
        $this->app->render('listeVille', [
            'villes' => $villes,
            'stats' => $stats,
            'success' => $success,
            'error' => $error,
            'baseUrl' => $this->getBaseUrl(),
            'nonce' => $this->getNonce()
        ]);
    }
}
