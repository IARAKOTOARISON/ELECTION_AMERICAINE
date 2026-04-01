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
            $id_etat = $_POST['id_etat'] ?? null;
            $votes = $_POST['votes'] ?? [];

            if (!$id_etat) {
                $_SESSION['error_message'] = 'L\'État est obligatoire';
                $this->app->redirect($this->getBaseUrl() . '/vote/saisie');
                return;
            }

            $hasVotes = false;
            foreach ($votes as $id_candidat => $nb_voix) {
                if ($nb_voix > 0) {
                    $hasVotes = true;
                    $data = [
                        'id_etat' => $id_etat,
                        'id_candidat' => $id_candidat,
                        'nb_voix' => $nb_voix
                    ];

                    // Vérifier si le vote existe déjà pour ce candidat dans cet État
                    $existingVoteId = $this->checkExistingVote($id_etat, $id_candidat);
                    
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
    private function checkExistingVote($id_etat, $id_candidat): ?int {
        $votes = $this->voteModel->getAllVotes();
        foreach ($votes as $vote) {
            if ($vote['id_etat'] == $id_etat && $vote['id_candidat'] == $id_candidat) {
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
                if ($vote['id_etat'] == $etatId) {
                    $totalVoixEtat += $vote['nb_voix'];
                }
            }

            $ligne = [
                'nomEtat' => $etat['nom'],
                'nbGrandsElecteurs' => $etat['nb_grands_electeurs'],
                'pourcentages' => []
            ];

            // Calculer les pourcentages par candidat
            foreach ($candidats as $candidat) {
                $voixCandidat = 0;
                foreach ($votes as $vote) {
                    if ($vote['id_etat'] == $etatId && $vote['id_candidat'] == $candidat['id']) {
                        $voixCandidat += $vote['nb_voix'];
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
                    if ($vote['id_etat'] == $etatId && $vote['id_candidat'] == $candidat['id']) {
                        $voixCandidat += $vote['nb_voix'];
                    }
                }

                if ($voixCandidat > $maxVoix) {
                    $maxVoix = $voixCandidat;
                    $candidatGagnant = $candidat['nom'];
                }
            }

            $resultats[] = [
                'nomEtat' => $etat['nom'],
                'nbGrandsElecteurs' => $etat['nb_grands_electeurs'],
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
