<?php

namespace app\controllers;

use flight\Engine;

/**
 * Contrôleur de base fournissant des fonctionnalités communes à tous les contrôleurs
 */
class BaseController {
    protected Engine $app;
    protected \PDO $db;

    public function __construct(\PDO $db, Engine $app) {
        $this->db = $db;
        $this->app = $app;
    }

    /**
     * Récupère l'URL de base de l'application
     * @return string Le chemin de base (ex: '' pour racine, '/COLLECTE_BNGRC' pour sous-dossier)
     */
    protected function getBaseUrl(): string {
        return $this->app->get('baseUrl') ?? '';
    }

    /**
     * Récupère le nonce CSP pour les scripts inline
     * @return string Le nonce généré pour cette requête
     */
    protected function getNonce(): string {
        return $this->app->get('csp_nonce') ?? '';
    }
}
