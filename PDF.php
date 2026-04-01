<?php

class PDF extends FPDF
{
    protected $pageTitle = '';

    public function setPageTitle($title) {
        $this->pageTitle = $title;
    }

    // En-tête
    function Header()
    {
        // Police Arial gras 15
        $this->SetFont('Arial', 'B', 12);

        // Couleur de texte bleu foncé
        $this->SetTextColor(0, 0, 128);
        
        // Titre
        if (!empty($this->pageTitle)) {
            $this->Cell(0, 10, $this->pageTitle, 0, 1, 'C');
        } else {
            $this->Cell(0, 10, 'Election Americaine 2026', 0, 1, 'C');
        }
        
        // Couleur de texte gris foncé
        $this->SetTextColor(128);
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, 'Rapport des Résultats', 0, 1, 'C');
        
        // Saut de ligne
        $this->Ln(4);
        
        // Ligne horizontale
        $this->SetDrawColor(0, 0, 128);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(4);
    }

    function titre($libelle)
    {
        // Arial 14
        $this->SetFont('Times', 'B', 14);
        $this->SetTextColor(0, 0, 128);

        $this->Cell(0, 8, $libelle, 0, 1, 'C');
        // Saut de ligne
        $this->Ln(3);
    }

    function sectionTitre($libelle)
    {
        // Arial 12
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 0, 128);
        $this->Cell(0, 7, $libelle, 0, 1, 'L');
        $this->Ln(2);
    }

    /**
     * Afficher le tableau des résultats par état
     */
    function tableauResultats($header, $data)
    {
        // Largeurs des colonnes
        $w = array(50, 30, 60, 40);

        // En-tête du tableau
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(0, 0, 128);
        $this->SetTextColor(255, 255, 255);
        
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 8, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();

        // Contenu du tableau
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0, 0, 0);
        
        foreach ($data as $row) {
            $this->Cell($w[0], 7, $row[0], 1, 0, 'L');
            $this->Cell($w[1], 7, $row[1], 1, 0, 'C');
            $this->Cell($w[2], 7, $row[2], 1, 0, 'L');
            $this->Cell($w[3], 7, $row[3], 1, 0, 'C');
            $this->Ln();
        }
    }

    /**
     * Afficher les états en égalité
     */
    function afficherEgalites($etatEgalite)
    {
        if (empty($etatEgalite)) {
            return;
        }

        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(255, 102, 0);
        $this->Cell(0, 7, '⚠ ÉTATS EN ÉGALITÉ (50-50) - À REVOTE:', 0, 1, 'L');
        
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0, 0, 0);
        
        foreach ($etatEgalite as $etat) {
            $this->Cell(0, 6, '   • ' . $etat, 0, 1, 'L');
        }
        
        $this->Ln(2);
    }

    /**
     * Afficher le vainqueur global
     */
    function afficherVainqueur($vainqueur)
    {
        if (!$vainqueur) {
            return;
        }

        // Boîte du vainqueur
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(0, 128, 0);
        
        $this->Cell(0, 8, 'VAINQUEUR DE L\'ÉLECTION', 0, 1, 'C');
        
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        
        $candidat = htmlspecialchars($vainqueur['candidat']);
        $grandsElecteurs = $vainqueur['grandsElecteurs'];
        $total = $vainqueur['totalGrandsElecteurs'];
        
        $this->Cell(0, 6, '« ' . $candidat . ' »', 0, 1, 'C');
        $this->Cell(0, 6, $grandsElecteurs . ' / ' . $total . ' Grands Électeurs', 0, 1, 'C');
        
        $this->Ln(3);
    }

    /**
     * Informations du rapport
     */
    function informationsRapport($dateGeneration)
    {
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(100, 100, 100);
        
        $this->Cell(0, 5, 'Rapport généré le : ' . $dateGeneration, 0, 1, 'L');
    }

    // Pied de page
    function Footer()
    {
        // Positionnement à 1,5 cm du bas
        $this->SetY(-15);

        // Police Arial italique 8
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(100, 100, 100);
        
        // Ligne horizontale
        $this->SetDrawColor(0, 0, 128);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->SetY($this->GetY() + 2);
        
        // Numéro de page
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

?>