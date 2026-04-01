<?php
namespace app\controllers;

use app\models\StatistiquesService;
use flight\Engine;

/**
 * RecapController — Contrôleur pour les récapitulatifs et statistiques
 */
class RecapController extends BaseController {
    private StatistiquesService $statsService;

    public function __construct(\PDO $db, Engine $app) {
        parent::__construct($db, $app);
        $this->statsService = new StatistiquesService($db);
    }

    /**
     * Retourne les statistiques globales (JSON)
     * Route: GET /api/stats ou /recap/stats
     */
    public function getStats(): void {
        try {
            $stats = $this->statsService->getRecapGlobal();
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Afficher la page de récapitulatif
     * Route: GET /recap
     */
    public function afficherRecap(): void {
        $stats = $this->statsService->getRecapGlobal();
        $graphiques = $this->statsService->getDonneesGraphiques();
        $financier = $this->statsService->getRecapFinancier();

        $this->app->render('recap', [
            'stats' => $stats,
            'graphiques' => $graphiques,
            'financier' => $financier,
            'baseUrl' => $this->getBaseUrl(),
            'nonce' => $this->getNonce()
        ]);
    }

    /**
     * Statistiques des besoins uniquement (JSON)
     */
    public function getStatsBesoins(): void {
        $stats = $this->statsService->getStatistiquesBesoins();
        header('Content-Type: application/json');
        echo json_encode($stats);
        exit;
    }

    /**
     * Statistiques des dons uniquement (JSON)
     */
    public function getStatsDons(): void {
        $stats = $this->statsService->getStatistiquesDons();
        header('Content-Type: application/json');
        echo json_encode($stats);
        exit;
    }

    /**
     * Statistiques des distributions (JSON)
     */
    public function getStatsDistributions(): void {
        $stats = $this->statsService->getStatistiquesDistributions();
        header('Content-Type: application/json');
        echo json_encode($stats);
        exit;
    }

    /**
     * Statistiques par ville (JSON)
     */
    public function getStatsParVille(): void {
        $stats = $this->statsService->getStatistiquesParVille();
        header('Content-Type: application/json');
        echo json_encode($stats);
        exit;
    }

    /**
     * Top produits demandés (JSON)
     */
    public function getTopProduits(): void {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $produits = $this->statsService->getTopProduitsDemandes($limit);
        header('Content-Type: application/json');
        echo json_encode($produits);
        exit;
    }

    /**
     * Évolution des distributions (JSON)
     */
    public function getEvolution(): void {
        $mois = isset($_GET['mois']) ? (int)$_GET['mois'] : 6;
        $evolution = $this->statsService->getEvolutionDistributions($mois);
        header('Content-Type: application/json');
        echo json_encode($evolution);
        exit;
    }

    /**
     * Récap financier (JSON)
     */
    public function getRecapFinancier(): void {
        $financier = $this->statsService->getRecapFinancier();
        header('Content-Type: application/json');
        echo json_encode($financier);
        exit;
    }
}
