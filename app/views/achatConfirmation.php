<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <title>Confirmation d'achat - BNGRC</title>
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
                    <h1 class="fw-bold">Confirmation de l'achat proposé</h1>
                </div>

                <?php if (empty($recap)): ?>
                    <div class="alert alert-info">Aucune proposition à confirmer.</div>
                <?php else: ?>
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Produit</th>
                                            <th>Quantité</th>
                                            <th>Prix Unitaire</th>
                                            <th>Coût</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $total = 0; foreach ($recap as $item):
                                            $prix = $item['prixUnitaire'] ?? 0;
                                            $q = $item['quantite'] ?? 0;
                                            $cout = $prix * $q;
                                            $total += $cout;
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['produit_nom'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($q) ?></td>
                                                <td><?= number_format($prix, 0, ',', ' ') ?> Ar</td>
                                                <td><?= number_format($cout, 0, ',', ' ') ?> Ar</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <?php $frais = $frais ?? 0; $grandTotal = $total + $frais; ?>
                                <div class="me-4 text-end">
                                    <div>Montant total: <strong><?= number_format($total, 0, ',', ' ') ?> Ar</strong></div>
                                    <div>Frais appliqués: <strong><?= number_format($frais, 0, ',', ' ') ?> Ar</strong></div>
                                    <div class="h5">Total à payer: <strong><?= number_format($grandTotal, 0, ',', ' ') ?> Ar</strong></div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-end">
                            <form method="post" action="<?= htmlspecialchars($base) ?>/achats/valider" class="me-2">
                                <!-- the controller expects relevant POST data; include a token or payload as needed -->
                                <button type="submit" class="btn btn-success">Confirmer</button>
                            </form>
                            <a href="<?= htmlspecialchars($base) ?>/achats" class="btn btn-secondary">Annuler</a>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <?php include __DIR__ . '/../../public/includes/footer.php'; ?>
    <script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
