<?php

use app\controllers\VoteController;
use app\controllers\EtatController;
use app\controllers\CandidatController;

use app\middlewares\SecurityHeadersMiddleware;
use flight\Engine;
use flight\net\Router;

/** 
 * @var Router $router 
 * @var Engine $app
 */

$router->group('', function(Router $router) use ($app) {

	// =========================================================================
	// ACCUEIL
	// =========================================================================
	$router->get('/', function() use ($app) {
		$baseUrl = $app->get('baseUrl') ?? '';
		$app->render('accueil', ['baseUrl' => $baseUrl]);
	});

	// =========================================================================
	// VOTES - Saisie et gestion des votes
	// =========================================================================
	
	// Page de saisie du nombre de voix
	$router->get('/vote/saisie', function() use ($app) {
		$db = $app->db();
		$controller = new VoteController($db, $app);
		$controller->afficherPageSaisie();
	});

	// Ajouter/enregistrer les votes pour un état
	$router->post('/vote/ajouter', function() use ($app) {
		$db = $app->db();
		$controller = new VoteController($db, $app);
		$controller->ajouter();
	});

	// Page des résultats
	$router->get('/vote/resultats', function() use ($app) {
		$db = $app->db();
		$controller = new VoteController($db, $app);
		$controller->afficherResultats();
	});

	// Exporter les résultats en PDF
	$router->get('/vote/export-pdf', function() use ($app) {
		$db = $app->db();
		$controller = new VoteController($db, $app);
		$controller->exporterPDF();
	});

	// API: Récupérer tous les votes (JSON)
	$router->get('/api/votes', function() use ($app) {
		$db = $app->db();
		$controller = new VoteController($db, $app);
		$controller->getAll();
	});

	// API: Récupérer un vote par ID (JSON)
	$router->get('/api/votes/@id', function(int $id) use ($app) {
		$db = $app->db();
		$controller = new VoteController($db, $app);
		$controller->getById($id);
	});

	// =========================================================================
	// ÉTATS - Gestion des états
	// =========================================================================
	
	// Page liste des états
	$router->get('/etat/liste', function() use ($app) {
		$db = $app->db();
		$controller = new EtatController($db, $app);
		$controller->afficherPage();
	});

	// API: Récupérer tous les états (JSON)
	$router->get('/api/etats', function() use ($app) {
		$db = $app->db();
		$controller = new EtatController($db, $app);
		$controller->getAll();
	});

	// API: Récupérer un état par ID (JSON)
	$router->get('/api/etats/@id', function(int $id) use ($app) {
		$db = $app->db();
		$controller = new EtatController($db, $app);
		$controller->getById($id);
	});

	// =========================================================================
	// CANDIDATS - Gestion des candidats
	// =========================================================================
	
	// Page liste des candidats
	$router->get('/candidat/liste', function() use ($app) {
		$db = $app->db();
		$controller = new CandidatController($db, $app);
		$controller->afficherPage();
	});

	// API: Récupérer tous les candidats (JSON)
	$router->get('/api/candidats', function() use ($app) {
		$db = $app->db();
		$controller = new CandidatController($db, $app);
		$controller->getAll();
	});

	// API: Récupérer un candidat par ID (JSON)
	$router->get('/api/candidats/@id', function(int $id) use ($app) {
		$db = $app->db();
		$controller = new CandidatController($db, $app);
		$controller->getById($id);
	});

}, [ SecurityHeadersMiddleware::class ]);
