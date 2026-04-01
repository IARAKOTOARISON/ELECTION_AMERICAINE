<?php
//require('fpdf/fpdf.php');

// Active l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//include('fpdf/fpdf.php') or die("Le fichier fpdf.php est introuvable à cet emplacement !");
require('./fpdf/fpdf.php');
require('./PDF.php');

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
//$pdf->Cell(40,10,'RELEVE DE NOTES ET RESULTATS');
$pdf->titre('RELEVE DE NOTES ET RESULTATS');

$pdf->informationsEtudiant('Rakotomalala', 'Jean', '01/01/2000', 'Antananarivo', '2020-12345');

$data = $pdf->LoadData('data.csv');
$header = array('UE', 'Intitule', 'Credit', 'Note/20', 'Resultat');
$footer=array('','Semestre 1','30','10,45','P');
$pdf->ImprovedTable($header,$data,$footer);
$pdf->Ln();

$data2 = $pdf->LoadData('data2.csv');
$footer2=array('','Semestre 2','30','10,98','P');
$pdf->ImprovedTable($header,$data2,$footer2);


$pdf->resultatGeneral();

$pdf->Output();

?>