<?php
namespace app\controllers;

use app\models\Candidat;
use flight\Engine;

class CandidatController extends BaseController {
    private Candidat $candidatModel;

    public function __construct(\PDO $db, Engine $app) {
        parent::__construct($db, $app);
        $this->candidatModel = new Candidat($db);
    }

    public function afficherPage(): void {
        $candidats = $this->candidatModel->getAllCandidats();

        $this->app->render('listeCandidats', [
            'candidats' => $candidats,
            'baseUrl' => $this->getBaseUrl(),
            'nonce' => $this->getNonce()
        ]);
    }

  
    public function getAll(): void {
        $candidats = $this->candidatModel->getAllCandidats();
        header('Content-Type: application/json');
        echo json_encode($candidats);
        exit;
    }

    /**
     * Récupérer un candidat par ID (JSON)
     */
    public function getById($id): void {
        $candidat = $this->candidatModel->getById($id);
        header('Content-Type: application/json');
        
        if ($candidat) {
            echo json_encode(['success' => true, 'data' => $candidat]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Candidat non trouvé']);
        }
        exit;
    }

    /**
     * Créer un nouveau candidat
     */
    public function creer(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => $_POST['nom'] ?? null,
                'prenom' => $_POST['prenom'] ?? null
            ];

            if ($data['nom'] && $data['prenom']) {
                if ($this->candidatModel->create($data)) {
                    $_SESSION['success_message'] = 'Candidat créé avec succès';
                    $this->app->redirect($this->getBaseUrl() . '/candidat');
                } else {
                    $_SESSION['error_message'] = 'Erreur lors de la création du candidat';
                }
            } else {
                $_SESSION['error_message'] = 'Le nom et le prénom du candidat sont obligatoires';
            }
        }
    }

    /**
     * Mettre à jour un candidat
     */
    public function modifier($id): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => $_POST['nom'] ?? null,
                'prenom' => $_POST['prenom'] ?? null
            ];

            if ($this->candidatModel->update($id, $data)) {
                $_SESSION['success_message'] = 'Candidat modifié avec succès';
                $this->app->redirect($this->getBaseUrl() . '/candidat');
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la modification du candidat';
            }
        }
    }

    /**
     * Supprimer un candidat
     */
    public function supprimer($id): void {
        if ($this->candidatModel->delete($id)) {
            $_SESSION['success_message'] = 'Candidat supprimé avec succès';
        } else {
            $_SESSION['error_message'] = 'Erreur lors de la suppression du candidat';
        }
        
        $this->app->redirect($this->getBaseUrl() . '/candidat');
    }
}
