<?php
namespace app\controllers;

use app\models\Etat;
use flight\Engine;

class EtatController extends BaseController {
    private Etat $etatModel;

    public function __construct(\PDO $db, Engine $app) {
        parent::__construct($db, $app);
        $this->etatModel = new Etat($db);
    }

    /**
     * Afficher la page des états
     */
    public function afficherPage(): void {
        $etats = $this->etatModel->getAllEtats();

        $this->app->render('listeEtats', [
            'etats' => $etats,
            'baseUrl' => $this->getBaseUrl(),
            'nonce' => $this->getNonce()
        ]);
    }

    /**
     * Récupérer tous les états (JSON)
     */
    public function getAll(): void {
        $etats = $this->etatModel->getAllEtats();
        header('Content-Type: application/json');
        echo json_encode($etats);
        exit;
    }

    /**
     * Récupérer un état par ID (JSON)
     */
    public function getById($id): void {
        $etat = $this->etatModel->getById($id);
        header('Content-Type: application/json');
        
        if ($etat) {
            echo json_encode(['success' => true, 'data' => $etat]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'État non trouvé']);
        }
        exit;
    }

    /**
     * Créer un nouvel état
     */
    public function creer(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => $_POST['nom'] ?? null,
                'nb_grands_electeurs' => $_POST['nb_grands_electeurs'] ?? 0
            ];

            if ($data['nom'] && $data['nb_grands_electeurs']) {
                if ($this->etatModel->create($data)) {
                    $_SESSION['success_message'] = 'État créé avec succès';
                    $this->app->redirect($this->getBaseUrl() . '/etat');
                } else {
                    $_SESSION['error_message'] = 'Erreur lors de la création de l\'état';
                }
            } else {
                $_SESSION['error_message'] = 'Tous les champs sont obligatoires';
            }
        }
    }

    /**
     * Mettre à jour un état
     */
    public function modifier($id): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => $_POST['nom'] ?? null,
                'nb_grands_electeurs' => $_POST['nb_grands_electeurs'] ?? 0
            ];

            if ($this->etatModel->update($id, $data)) {
                $_SESSION['success_message'] = 'État modifié avec succès';
                $this->app->redirect($this->getBaseUrl() . '/etat');
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la modification de l\'état';
            }
        }
    }

    /**
     * Supprimer un état
     */
    public function supprimer($id): void {
        if ($this->etatModel->delete($id)) {
            $_SESSION['success_message'] = 'État supprimé avec succès';
        } else {
            $_SESSION['error_message'] = 'Erreur lors de la suppression de l\'état';
        }
        
        $this->app->redirect($this->getBaseUrl() . '/etat');
    }
}
