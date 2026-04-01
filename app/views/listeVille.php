<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <title>Liste des Villes - BNGRC</title>
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
                        <h1 class="fw-bold">📊 Liste des Villes</h1>
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

                    <!-- Tableau des villes -->
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Liste complète des villes</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Nom de la ville</th>
                                            <th scope="col">Région</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($villes)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4">
                                                    <p class="text-muted mb-0">Aucune ville enregistrée pour le moment.</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($villes as $index => $ville): ?>
                                                <tr>
                                                    <th scope="row"><?= $index + 1 ?></th>
                                                    <td><strong><?= htmlspecialchars($ville['ville_nom']) ?></strong></td>
                                                    <td>
                                                        <span class="badge bg-info"><?= htmlspecialchars($ville['region_nom']) ?></span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" title="Voir les détails">
                                                            <i class="bi bi-eye"></i> Voir
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
<br><br><br>

                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card text-white bg-primary">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Villes</h6>
                                    <h2 class="display-5"><?= $stats['total_villes'] ?? 0 ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-success">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Régions</h6>
                                    <h2 class="display-5"><?= $stats['total_regions'] ?? 0 ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-info">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Moyenne Villes/Région</h6>
                                    <h2 class="display-5">
                                        <?php 
                                        $moyenne = ($stats['total_regions'] > 0) 
                                            ? round($stats['total_villes'] / $stats['total_regions'], 1) 
                                            : 0;
                                        echo $moyenne;
                                        ?>
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Répartition par région -->
                    <?php if (!empty($stats['villes_par_region'])): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Répartition par Région</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($stats['villes_par_region'] as $region => $count): ?>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="card border-info">
                                            <div class="card-body text-center">
                                                <h6 class="card-title text-info"><?= htmlspecialchars($region) ?></h6>
                                                <p class="card-text display-6"><?= $count ?> ville<?= $count > 1 ? 's' : '' ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>


                </div>
            </main>
        </div>
    </div>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . $base . '/includes/footer.php'; ?>
    
    <script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
