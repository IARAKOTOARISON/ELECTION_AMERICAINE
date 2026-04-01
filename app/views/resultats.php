<?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de l'Élection</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include(__DIR__ . '/../../includes/header.php'); ?>

    <div class="container mt-5">
        <h1 class="mb-4">Résultats de l'Élection 2026</h1>

        <?php if (!empty($etatEgalite)): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>Attention !</strong> Les états suivants ont une égalité (50-50) et doivent être revotés :
                <ul class="mb-0 mt-2">
                    <?php foreach ($etatEgalite as $etat): ?>
                        <li><?= htmlspecialchars($etat) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="mb-4">
            <h2 class="mb-3">Tableau du nombre de grands électeurs</h2>
            <?php if (!empty($resultats)): ?>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>État</th>
                            <th>Candidat</th>
                            <th>Nombre de Grands Électeurs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultats as $resultat): ?>
                            <tr>
                                <td><?= htmlspecialchars($resultat['nomEtat']) ?></td>
                                <td>
                                    <?php if ($resultat['candidatGagnant']): ?>
                                        <strong><?= htmlspecialchars($resultat['candidatGagnant']) ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">À déterminer</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($resultat['nbGrandsElecteurs']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">Aucun résultat disponible. Veuillez d'abord entrer des voix.</div>
            <?php endif; ?>
        </div>

        <!-- Déclaration du vainqueur -->
        <?php if (isset($vainqueurGlobal) && $vainqueurGlobal): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="text-align: center;">
                <h4 class="alert-heading">Vainqueur</h4>
                <p class="mb-0">
                    Le vainqueur est : <strong style="font-size: 1.2em;">« <?= htmlspecialchars($vainqueurGlobal['candidat']) ?> »</strong>
                </p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Bouton d'exportation PDF -->
        <div class="mt-4">
            <button class="btn btn-danger" onclick="exporterPDF()">
                Exporter les Résultats en PDF
            </button>
            <a href="<?= htmlspecialchars($base) ?>/vote/saisie" class="btn btn-primary">Retour à la Saisie</a>
            <a href="<?= htmlspecialchars($base) ?>/" class="btn btn-secondary">Accueil</a>
        </div>
    </div>

    <?php include(__DIR__ . '/../../includes/footer.php'); ?>

    <script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function exporterPDF() {
            window.location.href = '<?= htmlspecialchars($base) ?>/vote/export-pdf';
        }
    </script>
</body>
</html>
