<?php
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=BNGRC;charset=utf8mb4',
        'root',
        ''
    );
    echo "Connexion à la base réussie !<br>";
    var_dump($pdo);
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}
