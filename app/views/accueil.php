<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue</title>
    <?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <style>    </style>
</head>
<body>
    <section class="hero" role="img" aria-label="Image d'accueil">
        <div class="overlay" aria-hidden="true"></div>
        <div class="container">
            <h1>Bienvenue dans le site de l'election americaine 2026</h1>            
            <h3>Participez à l'élaboration de la politique de votre pays</h3>
            <p>Votez pour votre candidat préféré et faites entendre votre voix !</p>
            <a href="<?= htmlspecialchars($base) ?>/vote/saisie" class="btn btn-primary btn-lg mt-3">Votez !</a>
        </div>
    </section>
</body>
</html>