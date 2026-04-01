<?php
/**
 * Export PDF des résultats électoraux
 * Version finale - simple et directe
 */

// Supprime les avertissements
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

use app\models\Vote;
use app\models\Etat;
use app\models\Candidat;

class ExportPDFAlternatif {
    private $db;
    private $voteModel;
    private $etatModel;
    private $candidatModel;

    public function __construct($db) {
        $this->db = $db;
        $this->voteModel = new Vote($db);
        $this->etatModel = new Etat($db);
        $this->candidatModel = new Candidat($db);
    }

    public function genererPDF() {
        // Charger FPDF
        if (!class_exists('FPDF')) {
            $fpdfPath = __DIR__ . DIRECTORY_SEPARATOR . 'fpdf' . DIRECTORY_SEPARATOR . 'fpdf.php';
            if (!file_exists($fpdfPath)) {
                throw new Exception('FPDF non trouvé à: ' . $fpdfPath);
            }
            require_once $fpdfPath;
        }

        // Charger la classe PDF
        if (!class_exists('PDF')) {
            $pdfPath = __DIR__ . DIRECTORY_SEPARATOR . 'PDF.php';
            if (!file_exists($pdfPath)) {
                throw new Exception('PDF.php non trouvé à: ' . $pdfPath);
            }
            require_once $pdfPath;
        }

        try {
            // Récupérer les données
            $votes = $this->voteModel->getAllVotes();
            $etats = $this->etatModel->getAllEtats();
            $candidats = $this->candidatModel->getAllCandidats();

            $resultats = [];
            $etatEgalite = [];

            // Calculer résultats par état
            foreach ($etats as $etat) {
                $etatId = $etat['id'];
                
                $voixParCandidat = [];
                foreach ($candidats as $candidat) {
                    $voixCandidat = 0;
                    foreach ($votes as $vote) {
                        if ($vote['id_etat'] == $etatId && $vote['id_candidat'] == $candidat['id']) {
                            $voixCandidat += $vote['nb_voix'];
                        }
                    }
                    $voixParCandidat[$candidat['id']] = [
                        'nom' => $candidat['nom'],
                        'voix' => $voixCandidat
                    ];
                }

                $maxVoix = 0;
                $candidatGagnant = null;
                $countMaxVoix = 0;

                foreach ($voixParCandidat as $candidatId => $data) {
                    if ($data['voix'] > $maxVoix) {
                        $maxVoix = $data['voix'];
                        $candidatGagnant = $data['nom'];
                        $countMaxVoix = 1;
                    } elseif ($data['voix'] == $maxVoix && $maxVoix > 0) {
                        $countMaxVoix++;
                    }
                }

                if ($countMaxVoix > 1 && $maxVoix > 0) {
                    $etatEgalite[] = $etat['nom'];
                    $candidatGagnant = null;
                }

                $resultats[] = [
                    'nomEtat' => $etat['nom'],
                    'nbGrandsElecteurs' => $etat['nb_grands_electeurs'],
                    'candidatGagnant' => $candidatGagnant,
                    'voixGagnant' => $maxVoix
                ];
            }

            // Calculer le vainqueur global
            $grandsElecteursParCandidat = [];
            foreach ($resultats as $resultat) {
                if ($resultat['candidatGagnant']) {
                    $candidat = $resultat['candidatGagnant'];
                    if (!isset($grandsElecteursParCandidat[$candidat])) {
                        $grandsElecteursParCandidat[$candidat] = 0;
                    }
                    $grandsElecteursParCandidat[$candidat] += $resultat['nbGrandsElecteurs'];
                }
            }

            $vainqueurGlobal = null;
            if (!empty($grandsElecteursParCandidat)) {
                $maxGrandsElecteurs = 0;
                foreach ($grandsElecteursParCandidat as $candidat => $nb) {
                    if ($nb > $maxGrandsElecteurs) {
                        $maxGrandsElecteurs = $nb;
                        $vainqueurGlobal = [
                            'candidat' => $candidat,
                            'grandsElecteurs' => $maxGrandsElecteurs,
                            'totalGrandsElecteurs' => 539
                        ];
                    }
                }
            }

            // Générer le PDF
            $pdf = new PDF();
            $pdf->setPageTitle('Résultats de l\'Élection 2026');
            $pdf->AddPage();
            
            $pdf->titre('RÉSULTATS DE L\'ÉLECTION 2026');
            $pdf->Ln(3);

            if (!empty($etatEgalite)) {
                $pdf->sectionTitre('ATTENTION - Égalités détectées');
                $pdf->afficherEgalites($etatEgalite);
            }

            $pdf->sectionTitre('Tableau des Résultats par État');
            $header = array('État', 'G.É.', 'Candidat', 'Voix');
            $data = [];
            
            foreach ($resultats as $resultat) {
                $candidat = $resultat['candidatGagnant'] ?? 'À det.';
                $voix = $resultat['voixGagnant'] ?? 0;
                
                $data[] = array(
                    substr($resultat['nomEtat'], 0, 12),
                    (string)$resultat['nbGrandsElecteurs'],
                    substr($candidat, 0, 10),
                    (string)$voix
                );
            }
            
            $pdf->tableauResultats($header, $data);
            $pdf->Ln(4);

            if ($vainqueurGlobal) {
                $pdf->afficherVainqueur($vainqueurGlobal);
            }

            $pdf->informationsRapport(date('d/m/Y H:i:s'));

            // Envoyer le PDF en téléchargement
            $filename = 'Resultats_Election_' . date('Y-m-d_His') . '.pdf';
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            $pdf->Output('D', $filename);
            exit;
            
        } catch (Throwable $e) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>
