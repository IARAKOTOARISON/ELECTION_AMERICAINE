<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <title>Saisie des Besoins - BNGRC</title>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include $_SERVER['DOCUMENT_ROOT'] . $base . '/includes/header.php'; ?>

    <div class="container-fluid flex-grow-1">
        <div class="row h-100">

            <nav class="col-md-3 col-lg-2 bg-dark text-white p-3">
                <?php include $_SERVER['DOCUMENT_ROOT'] . $base . '/includes/menu.php'; ?>
            </nav>


            <main class="col-md-9 col-lg-10 p-4">
                <div class="container-fluid">
                    <h1 class="mb-4">Tableau de bord</h1>

                    <div class="row mb-4">
                        <div class="col-md-3 mb-2">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Villes</h6>
                                    <p class="h4 mb-0"><?= htmlspecialchars($stats['total_villes'] ?? 0) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Besoins</h6>
                                    <p class="h4 mb-0"><?= htmlspecialchars($stats['total_besoins'] ?? 0) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Dons</h6>
                                    <p class="h4 mb-0"><?= htmlspecialchars($stats['total_dons'] ?? 0) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Distributions</h6>
                                    <p class="h4 mb-0"><?= htmlspecialchars($stats['total_distributions'] ?? 0) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Détails par besoin</div>
                        <div class="card-body p-0">
                            <?php if (!empty($aboutVille) && is_array($aboutVille)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Ville</th>
                                                <th>Produit</th>
                                                <th>Besoin</th>
                                                <th>Don</th>
                                                <th>Reste</th>
                                                <th>Progression</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($aboutVille as $i => $b): ?>
                                                <tr>
                                                    <td><?= $i + 1 ?></td>
                                                    <td><?= htmlspecialchars($b['ville'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($b['produit'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($b['quantite'] ?? 0) ?></td>
                                                    <td><?= htmlspecialchars($b['quantite_distribuee'] ?? 0) ?></td>
                                                    <td><?= htmlspecialchars($b['reste'] ?? 0) ?></td>
                                                    <td><?= htmlspecialchars($b['progression'] ?? 0) ?>%</td>
                                                    <td><?= htmlspecialchars($b['statut'] ?? '') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="p-3">Aucune donnée disponible.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
        </div>
        </main>

    </div>
    <script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
</div>

<?php include __DIR__ . '/../../public/includes/footer.php'; ?>

<script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>