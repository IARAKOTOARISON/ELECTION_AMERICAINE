<?php
namespace app\controllers;

use app\models\Ville;
use app\models\Produit;
use app\models\StatusBesoin;
use app\models\Besoin;
use flight\Engine;

class BesoinController extends BaseController {
    private Ville $villeModel;
    private Produit $produitModel;
    private StatusBesoin $statusBesoinModel;
    private Besoin $besoinModel;

    public function __construct(\PDO $db, Engine $app) {
        parent::__construct($db, $app);
        $this->villeModel = new Ville($db);
        $this->produitModel = new Produit($db);
        $this->statusBesoinModel = new StatusBesoin($db);
        $this->besoinModel = new Besoin($db);
    }

    /**
     * Afficher le formulaire de saisie des besoins
     */
    public function afficherFormulaire(): void {
        // Charger les données nécessaires pour le formulaire
        $villes = $this->villeModel->getAllVilles();
        $produits = $this->produitModel->getAllProduits();
        $statusList = $this->statusBesoinModel->getAllStatusBesoin();

        // Récupérer les messages flash si présents
        $success = $_SESSION['success_message'] ?? null;
        $error = $_SESSION['error_message'] ?? null;
        
        // Nettoyer les messages flash
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        // Rendre la vue
        $this->app->render('besoinFormulaire', [
            'villes' => $villes,
            'produits' => $produits,
            'statusList' => $statusList,
            'success' => $success,
            'error' => $error,
            'baseUrl' => $this->getBaseUrl(),
            'nonce' => $this->getNonce()
        ]);
    }

    /**
     * Traiter l'ajout d'un nouveau besoin
     */
    public function ajouterBesoin(): void {
        try {
            // Récupérer les données du formulaire
            $idVille = $_POST['ville'] ?? null;
            $idProduit = $_POST['produit'] ?? null;
            $quantite = $_POST['quantite'] ?? null;
            $dateBesoin = $_POST['date'] ?? date('Y-m-d');
            
            // Validation basique
            if (empty($idVille) || empty($idProduit) || empty($quantite)) {
                throw new \Exception("Tous les champs obligatoires doivent être remplis.");
            }

            if ($quantite <= 0) {
                throw new \Exception("La quantité doit être supérieure à zéro.");
            }

            // Récupérer le status par défaut (par exemple "En attente" ou le premier status)
            $statusList = $this->statusBesoinModel->getAllStatusBesoin();
            $idStatus = !empty($statusList) ? $statusList[0]['id'] : 1;

            // Préparer les données pour l'insertion
            $data = [
                'idVille' => $idVille,
                'idProduit' => $idProduit,
                'quantite' => $quantite,
                'idStatus' => $idStatus,
                'dateBesoin' => $dateBesoin . ' ' . date('H:i:s')
            ];

            // Insérer dans la base de données
            $success = $this->besoinModel->create($data);

            if ($success) {
                $_SESSION['success_message'] = "Le besoin a été enregistré avec succès !";
            } else {
                throw new \Exception("Erreur lors de l'enregistrement du besoin.");
            }

        } catch (\Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
        }

        // Rediriger vers le formulaire
        $baseUrl = $this->getBaseUrl();
        $this->app->redirect($baseUrl . '/besoins/formulaire');
    }

    /**
     * Afficher la liste des besoins
     */
    public function afficherListe(): void {
        $besoins = $this->besoinModel->getAllBesoinsAvecDetails();
        
        // Calculer les statistiques
        $stats = [
            'total' => count($besoins),
            'attente' => 0,
            'partiel' => 0,
            'satisfait' => 0,
        ];
        
        foreach ($besoins as $besoin) {
            $status = strtolower($besoin['status_nom'] ?? '');
            if (strpos($status, 'attente') !== false || strpos($status, 'en cours') !== false) {
                $stats['attente']++;
            } elseif (strpos($status, 'partiel') !== false) {
                $stats['partiel']++;
            } elseif (strpos($status, 'satisfait') !== false || strpos($status, 'complet') !== false) {
                $stats['satisfait']++;
            }
        }
        
        $this->app->render('besoinListe', [
            'besoins' => $besoins,
            'stats' => $stats,
            'baseUrl' => $this->getBaseUrl(),
            'nonce' => $this->getNonce()
        ]);
    }
}
