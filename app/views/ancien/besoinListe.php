<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <title>Liste des Besoins - BNGRC</title>
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
                        <h1 class="fw-bold">Liste des Besoins Enregistrés</h1>
                        <a href="<?= htmlspecialchars($base) ?>/besoins/formulaire" class="btn btn-primary">
                            Ajouter un nouveau besoin
                        </a>
                    </div>

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
                        <div class="col-md-4">
                            <div class="card text-white bg-primary">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Besoins</h6>
                                    <h2 class="display-5"><?= $stats['total'] ?? 0 ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-warning">
                                <div class="card-body text-center">
                                    <h6 class="card-title">En Attente</h6>
                                    <h2 class="display-5"><?= $stats['attente'] ?? 0 ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-success">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Satisfaits</h6>
                                    <h2 class="display-5"><?= $stats['satisfait'] ?? 0 ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau des besoins -->
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white">
                            <h4 class="mb-0">Liste complète des besoins par ville</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Ville</th>
                                            <th scope="col">Produit</th>
                                            <th scope="col">Quantité</th>
                                            <th scope="col">Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($besoins) && is_array($besoins)): ?>
                                            <?php foreach ($besoins as $index => $besoin): ?>
                                                <?php
                                                    $status = strtolower($besoin['status_nom'] ?? '');
                                                    $badgeClass = 'bg-secondary';
                                                    if (strpos($status, 'attente') !== false || strpos($status, 'en cours') !== false) {
                                                        $badgeClass = 'bg-warning text-dark';
                                                    } elseif (strpos($status, 'partiel') !== false) {
                                                        $badgeClass = 'bg-info';
                                                    } elseif (strpos($status, 'satisfait') !== false || strpos($status, 'complet') !== false) {
                                                        $badgeClass = 'bg-success';
                                                    }
                                                    
                                                    $dateFormatted = date('d/m/Y', strtotime($besoin['dateBesoin'] ?? ''));
                                                ?>
                                                <tr>
                                                    <th scope="row"><?= $index + 1 ?></th>
                                                    <td><?= htmlspecialchars($dateFormatted) ?></td>
                                                    <td><strong><?= htmlspecialchars($besoin['ville_nom'] ?? 'N/A') ?></strong></td>
                                                    <td><?= htmlspecialchars($besoin['produit_nom'] ?? 'N/A') ?></td>
                                                    <td><?= htmlspecialchars($besoin['quantite'] ?? '0') ?></td>
                                                    <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($besoin['status_nom'] ?? 'N/A') ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <em class="text-muted">Aucun besoin enregistré pour le moment.</em>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination (à implémenter plus tard) -->
                            <?php if (!empty($besoins) && count($besoins) > 20): ?>
                            <nav aria-label="Navigation de la liste">
                                <ul class="pagination justify-content-center mt-3">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1">Précédent</a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Suivant</a>
                                    </li>
                                </ul>
                            </nav>
                            <?php endif; ?>
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
