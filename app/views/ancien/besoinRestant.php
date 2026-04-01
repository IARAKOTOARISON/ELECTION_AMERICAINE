<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
    <meta name="base-url" content="<?= htmlspecialchars($base) ?>">
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <title>Besoins restants - BNGRC</title>
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
                    <h1 class="fw-bold">📋 Besoins Restants</h1>
                    <a href="<?= htmlspecialchars($base) ?>/achats/proposer" class="btn btn-primary">
                        🛒 Effectuer un Achat
                    </a>
                </div>

                <?php if (isset($error) && $error): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <!-- Section: dons argent disponibles -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">💵 Dons Financiers Disponibles</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($donsDisponibles)): ?>
                            <div class="row">
                                <?php 
                                $totalArgent = 0;
                                foreach ($donsDisponibles as $don): 
                                    $totalArgent += ($don['montant'] ?? 0);
                                ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-success">
                                            <div class="card-body">
                                                <h6 class="card-title"><?= htmlspecialchars($don['donateur_nom'] ?? 'Don') ?></h6>
                                                <p class="text-muted small mb-1"><?= date('d/m/Y', strtotime($don['dateDon'] ?? '')) ?></p>
                                                <span class="badge bg-success fs-6">
                                                    <?= number_format($don['montant'] ?? 0, 0, ',', ' ') ?> Ar
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="alert alert-info mt-3 mb-0">
                                <strong>Total disponible:</strong> <?= number_format($totalArgent, 0, ',', ' ') ?> Ar
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                Aucun don financier disponible pour le moment.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tableau des besoins restants -->
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">🛒 Besoins Non Satisfaits</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($besoins)): ?>
                            <div class="alert alert-success mb-0">
                                <strong>Félicitations !</strong> Tous les besoins sont satisfaits.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Ville</th>
                                            <th>Produit</th>
                                            <th>Quantité</th>
                                            <th>Prix Unitaire</th>
                                            <th>Coût Estimé</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $totalCout = 0;
                                        foreach ($besoins as $b): 
                                            $prix = $b['prixUnitaire'] ?? 0;
                                            $quantite = $b['quantite'] ?? 0;
                                            $cout = $prix * $quantite;
                                            $totalCout += $cout;
                                        ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($b['dateBesoin'] ?? '')) ?></td>
                                                <td><strong><?= htmlspecialchars($b['ville_nom'] ?? '') ?></strong></td>
                                                <td><?= htmlspecialchars($b['produit_nom'] ?? '') ?></td>
                                                <td><span class="badge bg-warning text-dark"><?= number_format($quantite, 0, ',', ' ') ?></span></td>
                                                <td><?= number_format($prix, 0, ',', ' ') ?> Ar</td>
                                                <td><strong><?= number_format($cout, 0, ',', ' ') ?> Ar</strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-secondary">
                                        <tr>
                                            <th colspan="5" class="text-end">Total estimé:</th>
                                            <th><?= number_format($totalCout, 0, ',', ' ') ?> Ar</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <div class="text-center mt-4">
                                <a href="<?= htmlspecialchars($base) ?>/achats/proposer" class="btn btn-success btn-lg">
                                    🛒 Effectuer un Achat Manuel
                                </a>
                                <p class="text-muted mt-2">
                                    Convertissez vos dons financiers en matériel pour satisfaire ces besoins.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include __DIR__ . '/../../public/includes/footer.php'; ?>
    <script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
