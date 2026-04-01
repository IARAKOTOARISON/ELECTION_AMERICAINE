<?php
namespace app\controllers;

use app\models\Ville;
use app\models\Produit;
use app\models\StatusDon;
use app\models\Don;
use flight\Engine;

class DonController extends BaseController {
    private Ville $villeModel;
    private Produit $produitModel;
    private StatusDon $statusDonModel;
    private Don $donModel;

    public function __construct(\PDO $db, Engine $app) {
        parent::__construct($db, $app);
        $this->villeModel = new Ville($db);
        $this->produitModel = new Produit($db);
        $this->statusDonModel = new StatusDon($db);
        $this->donModel = new Don($db);
    }

    /**
     * Afficher le formulaire de saisie des dons
     */
    public function afficherFormulaire(): void {
        // Charger les données nécessaires pour le formulaire
        $villes = $this->villeModel->getAllVilles();
        $produits = $this->produitModel->getAllProduits();
        $statusList = $this->statusDonModel->getAllStatusDon();

        // Récupérer les messages flash si présents
        $success = $_SESSION['success_message'] ?? null;
        $error = $_SESSION['error_message'] ?? null;
        
        // Nettoyer les messages flash
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        // Rendre la vue
        $this->app->render('donFormulaire', [
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
     * Traiter l'ajout d'un nouveau don
     */
    public function ajouterDon(): void {
        try {
            // Récupérer les données du formulaire
            $typeDon = $_POST['typeDon'] ?? null;
            $dateDon = $_POST['dateDon'] ?? date('Y-m-d');
            $donateurNom = null;
            
            // Validation basique
            if (empty($typeDon)) {
                throw new \Exception("Le type de don est requis.");
            }

            // Récupérer le status par défaut (par exemple "Disponible" ou le premier status)
            $statusList = $this->statusDonModel->getAllStatusDon();
            $idStatus = !empty($statusList) ? $statusList[0]['id'] : 1;

            $data = [
                'idProduit' => null,
                'montant' => null,
                'quantite' => null,
                'dateDon' => $dateDon . ' ' . date('H:i:s'),
                'idStatus' => $idStatus,
                'donateur_nom' => null
            ];

            // Traiter selon le type de don
            if ($typeDon === 'nature') {
                // Don en nature
                $idProduit = $_POST['produitNature'] ?? null;
                $quantite = $_POST['quantiteNature'] ?? null;
                $donateurNom = $_POST['donateurNature'] ?? null;
                
                if (empty($idProduit) || empty($quantite) || empty($donateurNom)) {
                    throw new \Exception("Tous les champs requis pour un don en nature doivent être remplis.");
                }

                if ($quantite <= 0) {
                    throw new \Exception("La quantité doit être supérieure à zéro.");
                }

                $data['idProduit'] = $idProduit;
                $data['quantite'] = $quantite;
                $data['donateur_nom'] = $donateurNom;

            } elseif ($typeDon === 'argent') {
                // Don en argent
                $montant = $_POST['montant'] ?? null;
                $donateurNom = $_POST['donateurArgent'] ?? null;
                
                if (empty($montant) || empty($donateurNom)) {
                    throw new \Exception("Tous les champs requis pour un don en argent doivent être remplis.");
                }

                if ($montant <= 0) {
                    throw new \Exception("Le montant doit être supérieur à zéro.");
                }

                $data['montant'] = $montant;
                $data['donateur_nom'] = $donateurNom;
            } else {
                throw new \Exception("Type de don invalide.");
            }

            // Insérer dans la base de données
            $success = $this->donModel->create($data);

            if ($success) {
                $_SESSION['success_message'] = "Le don a été enregistré avec succès ! Merci pour votre générosité.";
            } else {
                throw new \Exception("Erreur lors de l'enregistrement du don.");
            }

        } catch (\Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
        }

        // Rediriger vers le formulaire
        $baseUrl = $this->getBaseUrl();
        $this->app->redirect($baseUrl . '/dons/formulaire');
    }

    /**
     * Afficher la liste des dons
     */
    public function afficherListe(): void {
        $dons = $this->donModel->getAllDonsAvecDetails();
        
        // Calculer les statistiques
        $stats = [
            'total' => count($dons),
            'nature' => 0,
            'argent' => 0,
            'montant_total' => 0
        ];
        
        foreach ($dons as $don) {
            if ($don['type_don'] === 'nature') {
                $stats['nature']++;
            } else {
                $stats['argent']++;
                $stats['montant_total'] += floatval($don['montant'] ?? 0);
            }
        }
        
        $this->app->render('donListe', [
            'dons' => $dons,
            'stats' => $stats,
            'baseUrl' => $this->getBaseUrl(),
            'nonce' => $this->getNonce()
        ]);
    }
}
