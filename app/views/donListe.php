<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <title>Liste des Dons - BNGRC</title>
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="fw-bold">Liste des Dons Reçus</h1>
                        <a href="<?= htmlspecialchars($base) ?>/dons/formulaire" class="btn btn-success">
                            Enregistrer un nouveau don
                        </a>
                    </div>

                    <?php if (isset($success) && $success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Succès!</strong> <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Erreur!</strong> <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Filtres -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <p class="text-muted mb-0">
                                <em>Filtres avancés disponibles prochainement</em>
                            </p>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-white bg-success">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Dons</h6>
                                    <h2 class="display-5"><?= $stats['total'] ?? 0 ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-info">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Dons en Nature</h6>
                                    <h2 class="display-5"><?= $stats['nature'] ?? 0 ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-warning">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Dons en Argent</h6>
                                    <h2 class="display-5"><?= $stats['argent'] ?? 0 ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-primary">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Montant Total</h6>
                                    <h2 class="display-6"><?= number_format($stats['montant_total'] ?? 0, 0, ',', ' ') ?> Ar</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau des dons -->
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0">Liste complète des dons reçus</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Donateur</th>
                                            <th scope="col">Type</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Quantité/Montant</th>
                                            <th scope="col">Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($dons)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <p class="text-muted mb-0">Aucun don enregistré pour le moment.</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($dons as $index => $don): ?>
                                                <tr>
                                                    <th scope="row"><?= $index + 1 ?></th>
                                                    <td><?= date('d/m/Y', strtotime($don['dateDon'])) ?></td>
                                                    <td><strong><?= htmlspecialchars($don['donateur_nom']) ?></strong></td>
                                                    <td>
                                                        <?php if ($don['type_don'] === 'nature'): ?>
                                                            <span class="badge bg-info">Nature</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning text-dark">Argent</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($don['type_don'] === 'nature'): ?>
                                                            <?= htmlspecialchars($don['produit_nom'] ?? 'Non spécifié') ?>
                                                        <?php else: ?>
                                                            Don financier
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($don['type_don'] === 'nature'): ?>
                                                            <?= htmlspecialchars($don['quantite']) ?>
                                                        <?php else: ?>
                                                            <?= number_format($don['montant'], 0, ',', ' ') ?> Ar
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $statusClass = 'bg-secondary';
                                                        $statusLower = strtolower($don['status_nom']);
                                                        if (strpos($statusLower, 'distribué') !== false || strpos($statusLower, 'satisf') !== false) {
                                                            $statusClass = 'bg-success';
                                                        } elseif (strpos($statusLower, 'cours') !== false || strpos($statusLower, 'partiel') !== false) {
                                                            $statusClass = 'bg-primary';
                                                        } elseif (strpos($statusLower, 'attente') !== false) {
                                                            $statusClass = 'bg-secondary';
                                                        }
                                                        ?>
                                                        <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($don['status_nom']) ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . $base . '/includes/footer.php'; ?>
    
    <script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
