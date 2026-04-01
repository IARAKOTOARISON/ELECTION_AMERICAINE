<?php
namespace app\controllers;

use app\models\Vote;
use app\models\Etat;
use app\models\Candidat;
use flight\Engine;

class VoteController extends BaseController {
    private Vote $voteModel;
    private Etat $etatModel;
    private Candidat $candidatModel;

    public function __construct(\PDO $db, Engine $app) {
        parent::__construct($db, $app);
        $this->voteModel = new Vote($db);
        $this->etatModel = new Etat($db);
        $this->candidatModel = new Candidat($db);
    }

    /**
     * Afficher la page de saisie du nombre de voix
     */
    public function afficherPageSaisie(): void {
        $etats = $this->etatModel->getAllEtats();
        $candidats = $this->candidatModel->getAllCandidats();
        $percentages = $this->calculerPourcentages();

        $success = $_SESSION['success_message'] ?? null;
        $error = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $this->app->render('saisieNbVoix', [
            'etats' => $etats,
            'candidats' => $candidats,
            'percentages' => $percentages,
            'success' => $success,
            'error' => $error,
            'baseUrl' => $this->getBaseUrl(),
            'nonce' => $this->getNonce()
        ]);
    }

    /**
     * Ajouter les votes pour un État (multiple candidats)
     */
    public function ajouter(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idEtat = $_POST['idEtat'] ?? null;
            $votes = $_POST['votes'] ?? [];

            if (!$idEtat) {
                $_SESSION['error_message'] = 'L\'État est obligatoire';
                $this->app->redirect($this->getBaseUrl() . '/vote/saisie');
                return;
            }

            $hasVotes = false;
            foreach ($votes as $idCandidat => $nbVoix) {
                if ($nbVoix > 0) {
                    $hasVotes = true;
                    $data = [
                        'idEtat' => $idEtat,
                        'idCandidat' => $idCandidat,
                        'nbVoix' => $nbVoix
                    ];

                    // Vérifier si le vote existe déjà pour ce candidat dans cet État
                    $existingVoteId = $this->checkExistingVote($idEtat, $idCandidat);
                    
                    if ($existingVoteId) {
                        // Mettre à jour le vote existant
                        $this->voteModel->update($existingVoteId, $data);
                    } else {
                        // Créer un nouveau vote
                        $this->voteModel->create($data);
                    }
                }
            }

            if ($hasVotes) {
                $_SESSION['success_message'] = 'Votes enregistrés avec succès';
            } else {
                $_SESSION['error_message'] = 'Aucun vote n\'a été enregistré';
            }
        }
        
        $this->app->redirect($this->getBaseUrl() . '/vote/saisie');
    }

    /**
     * Vérifier si un vote existe déjà pour un candidat dans un État
     */
    private function checkExistingVote($idEtat, $idCandidat): ?int {
        $votes = $this->voteModel->getAllVotes();
        foreach ($votes as $vote) {
            if ($vote['idEtat'] == $idEtat && $vote['idCandidat'] == $idCandidat) {
                return $vote['id'];
            }
        }
        return null;
    }

    /**
     * Récupérer tous les votes (JSON)
     */
    public function getAll(): void {
        $votes = $this->voteModel->getAllVotes();
        header('Content-Type: application/json');
        echo json_encode($votes);
        exit;
    }

    /**
     * Récupérer un vote par ID (JSON)
     */
    public function getById($id): void {
        $vote = $this->voteModel->getById($id);
        header('Content-Type: application/json');
        
        if ($vote) {
            echo json_encode(['success' => true, 'data' => $vote]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Vote non trouvé']);
        }
        exit;
    }

    /**
     * Afficher la page des résultats
     */
    public function afficherResultats(): void {
        $votes = $this->voteModel->getAllVotes();
        $resultats = $this->calculerResultats($votes);

        $this->app->render('resultats', [
            'resultats' => $resultats,
            'baseUrl' => $this->getBaseUrl(),
            'nonce' => $this->getNonce()
        ]);
    }

    /**
     * Calculer les pourcentages par état et candidat
     */
    private function calculerPourcentages(): array {
        $etats = $this->etatModel->getAllEtats();
        $candidats = $this->candidatModel->getAllCandidats();
        $votes = $this->voteModel->getAllVotes();

        $percentages = [];

        foreach ($etats as $etat) {
            $etatId = $etat['id'];
            $totalVoixEtat = 0;

            // Calculer le total des voix pour cet état
            foreach ($votes as $vote) {
                if ($vote['idEtat'] == $etatId) {
                    $totalVoixEtat += $vote['nbVoix'];
                }
            }

            $ligne = [
                'nomEtat' => $etat['nom'],
                'nbGrandsElecteurs' => $etat['nbGrandsElecteurs'],
                'pourcentages' => []
            ];

            // Calculer les pourcentages par candidat
            foreach ($candidats as $candidat) {
                $voixCandidat = 0;
                foreach ($votes as $vote) {
                    if ($vote['idEtat'] == $etatId && $vote['idCandidat'] == $candidat['id']) {
                        $voixCandidat += $vote['nbVoix'];
                    }
                }

                $pourcentage = $totalVoixEtat > 0 ? ($voixCandidat / $totalVoixEtat) * 100 : 0;
                $ligne['pourcentages'][] = $pourcentage;
            }

            $percentages[] = $ligne;
        }

        return $percentages;
    }

    /**
     * Calculer les résultats finaux
     */
    private function calculerResultats($votes): array {
        $etats = $this->etatModel->getAllEtats();
        $candidats = $this->candidatModel->getAllCandidats();

        $resultats = [];

        foreach ($etats as $etat) {
            $etatId = $etat['id'];
            $maxVoix = 0;
            $candidatGagnant = null;

            foreach ($candidats as $candidat) {
                $voixCandidat = 0;
                foreach ($votes as $vote) {
                    if ($vote['idEtat'] == $etatId && $vote['idCandidat'] == $candidat['id']) {
                        $voixCandidat += $vote['nbVoix'];
                    }
                }

                if ($voixCandidat > $maxVoix) {
                    $maxVoix = $voixCandidat;
                    $candidatGagnant = $candidat['nom'];
                }
            }

            $resultats[] = [
                'nomEtat' => $etat['nom'],
                'nbGrandsElecteurs' => $etat['nbGrandsElecteurs'],
                'candidatGagnant' => $candidatGagnant,
                'voixGagnant' => $maxVoix
            ];
        }

        return $resultats;
    }

    /**
     * Mettre à jour un vote
     */
    public function modifier($id): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'idEtat' => $_POST['idEtat'] ?? null,
                'idCandidat' => $_POST['idCandidat'] ?? null,
                'nbVoix' => $_POST['nbVoix'] ?? 0
            ];

            if ($this->voteModel->update($id, $data)) {
                $_SESSION['success_message'] = 'Vote modifié avec succès';
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la modification du vote';
            }
        }
        
        $this->app->redirect($this->getBaseUrl() . '/vote/saisie');
    }

    /**
     * Supprimer un vote
     */
    public function supprimer($id): void {
        if ($this->voteModel->delete($id)) {
            $_SESSION['success_message'] = 'Vote supprimé avec succès';
        } else {
            $_SESSION['error_message'] = 'Erreur lors de la suppression du vote';
        }
        
        $this->app->redirect($this->getBaseUrl() . '/vote/saisie');
    }
}
