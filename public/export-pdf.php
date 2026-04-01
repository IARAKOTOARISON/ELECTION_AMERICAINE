<?php
/**
 * Handler pour l'export PDF des résultats électoraux
 * Cet entry point fonctionne indépendamment de Flight pour maxiumum de compatibilité
 */

error_reporting(0);
ini_set('display_errors', 0);

try {
    // Chemins corrects (public/ est le dossier courant)
    $ds = DIRECTORY_SEPARATOR;
    $rootDir = dirname(__DIR__);
    
    // 1. Charger l'autoloader Composer
    require_once $rootDir . $ds . 'vendor' . $ds . 'autoload.php';
    
    // 2. Charger l'autoloader custom pour les modèles
    spl_autoload_register(function ($class) use ($rootDir, $ds) {
        $baseDir = $rootDir . $ds . 'app' . $ds;
        $classLower = str_replace(['app\\', 'App\\'], '', $class);
        $classLower = str_replace('\\', $ds, $classLower);
        $file = $baseDir . $classLower . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });
    
    // 3. Charger la configuration
    $config = require $rootDir . $ds . 'app' . $ds . 'config' . $ds . 'config.php';
    
    // 4. Charger la classe d'export PDF
    require_once $rootDir . $ds . 'exportPDFAlternatif.php';
    
    // 5. Établir connexion PDO
    $dsn = 'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['dbname'] . ';charset=utf8mb4;unix_socket=/opt/lampp/var/mysql/mysql.sock';
    $db = new PDO(
        $dsn,
        $config['database']['user'] ?? null,
        $config['database']['password'] ?? null,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // 6. Générer le PDF
    $exporteur = new ExportPDFAlternatif($db);
    $exporteur->genererPDF();
    
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    die('❌ Erreur lors de la génération du PDF:' . PHP_EOL . $e->getMessage());
}
?>
