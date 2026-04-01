<?php
namespace app\controllers;

use app\models\Besoin;
use app\models\Don;
use app\models\Distribution;
use app\models\Reinitialize;
use flight\Engine;

class ReinitializeController extends BaseController {

    public function __construct(\PDO $db, Engine $app) {
        parent::__construct($db, $app);
    }   


    public function reanitialize(){
        // S'assurer que la session est démarrée pour stocker les messages flash
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        try {
            $reinitializeModel = new Reinitialize($this->db);
            $reinitializeModel->reinitialize();

            $_SESSION['success_message'] = "La base de données a été réinitialisée avec succès !";
        } catch (\Exception $e) {
            $_SESSION['error_message'] = "Erreur lors de la réinitialisation : " . $e->getMessage();
        }

        // Rediriger vers la page d'accueil ou une autre page appropriée
        $baseUrl = $this->getBaseUrl();
        $this->app->redirect($baseUrl . '/simulation');
    }
   
}
