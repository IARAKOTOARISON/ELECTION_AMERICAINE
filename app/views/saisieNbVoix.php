<?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saisie du Nombre de Voix</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <style>
        .form-section {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .radio-group {
            display: flex;
            gap: 30px;
            margin-top: 15px;
        }
        .radio-group label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-weight: 500;
        }
        .percentage-table {
            margin-top: 40px;
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
        .success-message {
            display: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include('../../includes/header.php'); ?>

    <div class="container mt-5">
        <h1 class="mb-4">Saisie du Nombre de Voix</h1>

        <!-- Messages de succès/erreur -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Formulaire de saisie -->
        <div class="form-section">
            <form id="voteForm" method="POST" action="<?= htmlspecialchars($base) ?>/vote/ajouter">
                
                <!-- Sélection de l'État -->
                <div class="mb-4">
                    <label for="etatSelect" class="form-label">Sélectionner un État:</label>
                    <select id="etatSelect" name="idEtat" class="form-select" required>
                        <option value="">-- Choisir un État --</option>
                        <?php if (!empty($etats)): ?>
                            <?php foreach ($etats as $etat): ?>
                                <option value="<?= htmlspecialchars($etat['id']) ?>">
                                    <?= htmlspecialchars($etat['nom']) ?> (<?= htmlspecialchars($etat['nbGrandsElecteurs']) ?> électeurs)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Sélection du Candidat (boutons radios) -->
                <div class="mb-4">
                    <label class="form-label">Sélectionner un candidat (une seule sélection):</label>
                    <div class="radio-group">
                        <?php if (!empty($candidats)): ?>
                            <?php foreach ($candidats as $candidat): ?>
                                <label>
                                    <input type="radio" name="idCandidat" value="<?= htmlspecialchars($candidat['id']) ?>" required>
                                    <?= htmlspecialchars($candidat['nom']) ?>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Saisie du nombre de voix -->
                <div class="mb-4">
                    <label for="nbVoix" class="form-label">Nombre de voix:</label>
                    <input type="number" id="nbVoix" name="nbVoix" class="form-control" min="0" required>
                </div>

                <!-- Bouton valider -->
                <button type="submit" class="btn btn-primary">Valider</button>
            </form>
        </div>

        <!-- Tableau de pourcentage -->
        <div class="percentage-table">
            <h2 class="mb-4">Tableau des Pourcentages par État</h2>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>État</th>
                        <th>Grands Électeurs</th>
                        <?php if (!empty($candidats)): ?>
                            <?php foreach ($candidats as $candidat): ?>
                                <th>% <?= htmlspecialchars($candidat['nom']) ?></th>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($percentages)): ?>
                        <?php foreach ($percentages as $ligne): ?>
                            <tr>
                                <td><?= htmlspecialchars($ligne['nomEtat']) ?></td>
                                <td><?= htmlspecialchars($ligne['nbGrandsElecteurs']) ?></td>
                                <?php foreach ($ligne['pourcentages'] as $pourcentage): ?>
                                    <td><?= number_format($pourcentage, 2) ?>%</td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="100" class="text-center text-muted">Aucune donnée disponible</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Lien vers les résultats -->
            <div class="mt-4">
                <a href="<?= htmlspecialchars($base) ?>/resultats" class="btn btn-info">Voir les Résultats Complets</a>
            </div>
        </div>
    </div>

    <?php include('../../includes/footer.php'); ?>

    <script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= htmlspecialchars($nonce) ?>">
        document.getElementById('voteForm').addEventListener('submit', function(e) {
            if (!document.getElementById('nbVoix').value) {
                e.preventDefault();
                alert('Veuillez entrer un nombre de voix valide');
            }
        });
    </script>
</body>
</html>
