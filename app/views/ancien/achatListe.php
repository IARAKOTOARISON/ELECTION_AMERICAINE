<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <title>Historique des Achats - BNGRC</title>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/../../public/includes/header.php'; ?>

    <div class="container-fluid flex-grow-1">
        <div class="row h-100">
            <nav class="col-md-3 col-lg-2 bg-dark text-white p-3">
                <?php include __DIR__ . '/../../public/includes/menu.php'; ?>
            </nav>

            <main class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="fw-bold">Liste des Achats</h1>
                </div>

                <!-- Filtre par ville -->
                <div class="mb-3">
                    <form method="get" action="">
                        <div class="row g-2 align-items-center">
                            <div class="col-auto">
                                <label for="villeFilter" class="col-form-label">Filtrer par ville :</label>
                            </div>
                            <div class="col-auto">
                                <select id="villeFilter" name="ville" class="form-select">
                                    <option value="">Toutes</option>
                                    <?php if (!empty($villes)): foreach ($villes as $v): ?>
                                        <option value="<?= (int)$v['id'] ?>" <?= (isset($villeSelectionnee) && $villeSelectionnee == $v['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($v['nom']) ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-primary">Filtrer</button>
                            </div>
                            <?php if (!empty($villeSelectionnee)): ?>
                            <div class="col-auto">
                                <a href="<?= htmlspecialchars($base) ?>/achats" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">Historique des achats</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Ville</th>
                                        <th>Produit</th>
                                        <th>Qté</th>
                                        <th>Coût</th>
                                        <th>Frais</th>
                                        <th>Total</th>
                                        <th>Don utilisé</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($achats)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">Aucun achat enregistré.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($achats as $a): ?>
                                            <tr>
                                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($a['date_achat'] ?? $a['date'] ?? ''))) ?></td>
                                                <td><?= htmlspecialchars($a['ville_nom'] ?? $a['ville'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($a['produit_nom'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($a['quantite'] ?? $a['qte'] ?? '') ?></td>
                                                <td><?= number_format($a['montant_total'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                <td><?= number_format($a['frais_appliques'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                <td><?= number_format((($a['montant_total'] ?? 0) + ($a['frais_appliques'] ?? 0)), 0, ',', ' ') ?> Ar</td>
                                                <td><?= htmlspecialchars($a['don_nom'] ?? $a['donateur'] ?? '') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include __DIR__ . '/../../public/includes/footer.php'; ?>
    <script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/achatListe.js"></script>
</body>

</html>
