<?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de l'Élection</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <style>
        .results-section {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px 30px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .winner-section {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 30px;
        }
        .winner-name {
            font-size: 28px;
            font-weight: bold;
            color: #155724;
            margin: 10px 0;
        }
        .export-btn {
            margin-top: 20px;
        }
    </style>
</head>
<body>
        <?php include(__DIR__ . '/../../includes/header.php'); ?>
        <h1 class="mb-4">Résultats de l'Élection 2026</h1>

        <!-- Section Déclaration du Vainqueur -->
        <div class="winner-section">
            <h3>Vainqueur Général</h3>
            <div class="winner-name">
                Le vainqueur est : 
                <span style="color: #28a745;">
                    « <?= isset($vainqueurGlobal) && $vainqueurGlobal ? htmlspecialchars($vainqueurGlobal['candidat']) : 'À déterminer' ?> »
                </span>
            </div>
            <?php if (isset($vainqueurGlobal) && $vainqueurGlobal): ?>
                <p class="mb-0">
                    avec <strong><?= htmlspecialchars($vainqueurGlobal['grandsElecteurs']) ?></strong> 
                    grand<?= $vainqueurGlobal['grandsElecteurs'] > 1 ? 's' : '' ?> électeur<?= $vainqueurGlobal['grandsElecteurs'] > 1 ? 's' : '' ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Tableau du nombre de grands électeurs -->
        <div class="results-section">
            <h2 class="mb-4">Tableau du Nombre de Grands Électeurs par État</h2>
            
            <?php if (!empty($resultats)): ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>État</th>
                            <th>Grands Électeurs</th>
                            <th>Candidat Gagnant</th>
                            <th>Nombre de Voix</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultats as $resultat): ?>
                            <tr>
                                <td><?= htmlspecialchars($resultat['nomEtat']) ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= htmlspecialchars($resultat['nbGrandsElecteurs']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($resultat['candidatGagnant']): ?>
                                        <span class="badge bg-success">
                                            <?= htmlspecialchars($resultat['candidatGagnant']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Aucun vote</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($resultat['voixGagnant']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">Aucun résultat disponible. Veuillez d'abord entrer des voix.</div>
            <?php endif; ?>
        </div>

        <!-- Bouton d'exportation PDF -->
        <div class="export-btn">
            <button class="btn btn-danger" onclick="exporterPDF()">
                 Exporter les Résultats en PDF
            </button>
        </div>

        <!-- Liens de navigation -->
        <div class="mt-4">
            <a href="<?= htmlspecialchars($base) ?>/vote/saisie" class="btn btn-primary">Retour à la Saisie</a>
            <a href="<?= htmlspecialchars($base) ?>/" class="btn btn-secondary">Accueil</a>
        </div>
    </div>

    <?php include(__DIR__ . '/../../includes/footer.php'); ?>

    <script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function exporterPDF() {
            // Redirection vers la route d'export PDF
            window.location.href = '<?= htmlspecialchars($base) ?>/vote/export-pdf';
        }
    </script>
</body>
</html>
