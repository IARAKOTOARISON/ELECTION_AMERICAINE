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
        <?php include(__DIR__ . '/../../includes/header.php'); ?>
        <h1 class="mb-4">Saisie du Nombre de Voix</h1>

        
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

        <div class="form-section">
            <form id="voteForm" method="POST" action="<?= htmlspecialchars($base) ?>/vote/ajouter">
                
               
                <div class="mb-4">
                    <label for="etatSelect" class="form-label">Sélectionner un État:</label>
                    <select id="etatSelect" name="id_etat" class="form-select" required>
                        <option value="">-- Choisir un État --</option>
                        <?php if (!empty($etats)): ?>
                            <?php foreach ($etats as $etat): ?>
                                <option value="<?= htmlspecialchars($etat['id']) ?>">
                                    <?= htmlspecialchars($etat['nom']) ?> (<?= htmlspecialchars($etat['nb_grands_electeurs']) ?> électeurs)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Nombre de personnes ayant voté par candidat:</label>
                    <div class="row g-3">
                        <?php if (!empty($candidats)): ?>
                            <?php foreach ($candidats as $candidat): ?>
                                <div class="col-md-6">
                                    <label for="candidat_<?= htmlspecialchars($candidat['id']) ?>" class="form-label">
                                        Nombre de voix - <?= htmlspecialchars($candidat['nom']) ?>:
                                    </label>
                                    <input type="number" 
                                           id="candidat_<?= htmlspecialchars($candidat['id']) ?>" 
                                           name="votes[<?= htmlspecialchars($candidat['id']) ?>]" 
                                           class="form-control" 
                                           min="0" 
                                           value="0">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
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
                <a href="<?= htmlspecialchars($base) ?>/vote/resultats" class="btn btn-info">Voir les Résultats Complets</a>
            </div>
        </div>
    </div>

    <?php include(__DIR__ . '/../../includes/footer.php'); ?>

    <script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= htmlspecialchars($nonce) ?>">
        document.getElementById('voteForm').addEventListener('submit', function(e) {
            const inputs = document.querySelectorAll('input[name^="votes["]');
            let hasVotes = false;
            
            inputs.forEach(input => {
                if (parseInt(input.value) > 0) {
                    hasVotes = true;
                }
            });
            
            if (!hasVotes) {
                e.preventDefault();
                alert('Veuillez entrer au moins un nombre de voix pour un candidat');
            }
        });
    </script>
</body>
</html>
