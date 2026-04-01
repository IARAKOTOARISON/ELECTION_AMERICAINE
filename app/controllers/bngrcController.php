<?php

namespace app\controllers;

use flight\Engine;

class ApiExampleController {

	protected Engine $app;

	public function __construct($app) {
		$this->app = $app;
	}


    protected function getBaseUrl() {
        // Chemin depuis la racine du serveur
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';

        // Obtenir le répertoire du script (sans le nom du fichier)
        $basePath = dirname($scriptName);

        // Normaliser
        if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
            $basePath = '';
        }
        return $basePath;
    }

}