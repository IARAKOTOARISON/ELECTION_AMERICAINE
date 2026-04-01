<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
    <meta name="base-url" content="<?= htmlspecialchars($base) ?>">
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <title>Récapitulatif - BNGRC</title>
    <style>
        .stat-card {
            transition: transform 0.2s ease-in-out;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .progress {
            height: 25px;
            border-radius: 15px;
        }
        .progress-bar {
            border-radius: 15px;
            font-weight: bold;
        }
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .loader-overlay.active {
            display: flex;
        }
        .loader-content {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
        }
        .spinner-large {
            width: 60px;
            height: 60px;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/../../public/includes/header.php'; ?>
    
    <!-- Loader/Spinner Overlay -->
    <div class="loader-overlay" id="loaderOverlay">
        <div class="loader-content">
            <div class="spinner-border text-primary spinner-large" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-3 mb-0 fw-bold">Actualisation des données...</p>
        </div>
    </div>
    
    <div class="container-fluid flex-grow-1">
        <div class="row h-100">
           
            <nav class="col-md-3 col-lg-2 bg-dark text-white p-3">
                <?php include __DIR__ . '/../../public/includes/menu.php'; ?>
            </nav>
            
            <main class="col-md-9 col-lg-10 p-4">
                <div class="container-fluid">
                    <!-- En-tête avec bouton Actualiser -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="fw-bold">📊 Récapitulatif Global</h1>
                        <button type="button" id="btnActualiser" class="btn btn-primary btn-lg">
                            <span class="spinner-border spinner-border-sm d-none me-2" role="status" id="spinnerActualiser"></span>
                            🔄 ACTUALISER (AJAX)
                        </button>
                    </div>

                    <!-- Indicateurs Besoins (EN MONTANT - conformément au sujet) -->
                    <div class="row mb-4">
                        <!-- Montant Total des Besoins -->
                        <div class="col-md-4 mb-3">
                            <div class="card stat-card border-primary h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-primary text-uppercase">Montant Total Besoins</h6>
                                    <h2 class="display-5 fw-bold" id="besoinsMontantTotal">
                                        <?= number_format($stats['besoins']['montant_total'] ?? 0, 0, ',', ' ') ?>
                                    </h2>
                                    <small class="text-muted">Ariary (<?= $stats['besoins']['total'] ?? 0 ?> besoins)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Montant Besoins Satisfaits -->
                        <div class="col-md-4 mb-3">
                            <div class="card stat-card border-success h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-success text-uppercase">Montant Besoins Satisfaits</h6>
                                    <h2 class="display-5 fw-bold" id="besoinsMontantSatisfaits">
                                        <?= number_format($stats['besoins']['montant_satisfaits'] ?? 0, 0, ',', ' ') ?>
                                    </h2>
                                    <small class="text-muted">Ariary (<?= $stats['besoins']['satisfaits'] ?? 0 ?> besoins couverts)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Montant Besoins Restants -->
                        <div class="col-md-4 mb-3">
                            <div class="card stat-card border-warning h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-warning text-uppercase">Montant Besoins Restants</h6>
                                    <h2 class="display-5 fw-bold" id="besoinsMontantRestants">
                                        <?= number_format($stats['besoins']['montant_restants'] ?? 0, 0, ',', ' ') ?>
                                    </h2>
                                    <small class="text-muted">Ariary (<?= $stats['besoins']['en_attente'] ?? 0 ?> besoins en attente)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Barres de Progression CSS -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">📈 Progression Globale</h5>
                        </div>
                        <div class="card-body">
                            <!-- Barre Besoins Satisfaits -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold">Besoins Satisfaits</span>
                                    <span id="pourcentageBesoins"><?= $stats['besoins']['pourcentage_satisfaits'] ?? 0 ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         id="barreBesoins"
                                         style="width: <?= $stats['besoins']['pourcentage_satisfaits'] ?? 0 ?>%;" 
                                         aria-valuenow="<?= $stats['besoins']['pourcentage_satisfaits'] ?? 0 ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        <?= $stats['besoins']['pourcentage_satisfaits'] ?? 0 ?>%
                                    </div>
                                </div>
                            </div>

                            <!-- Barre Dons Distribués -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold">Dons Distribués</span>
                                    <span id="pourcentageDons"><?= $stats['dons']['pourcentage_distribues'] ?? 0 ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                         id="barreDons"
                                         style="width: <?= $stats['dons']['pourcentage_distribues'] ?? 0 ?>%;" 
                                         aria-valuenow="<?= $stats['dons']['pourcentage_distribues'] ?? 0 ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        <?= $stats['dons']['pourcentage_distribues'] ?? 0 ?>%
                                    </div>
                                </div>
                            </div>

                            <!-- Barre Distributions Confirmées -->
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold">Distributions Confirmées</span>
                                    <span id="pourcentageDistributions"><?= $stats['distributions']['pourcentage_confirmees'] ?? 0 ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         id="barreDistributions"
                                         style="width: <?= $stats['distributions']['pourcentage_confirmees'] ?? 0 ?>%;" 
                                         aria-valuenow="<?= $stats['distributions']['pourcentage_confirmees'] ?? 0 ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        <?= $stats['distributions']['pourcentage_confirmees'] ?? 0 ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques détaillées -->
                    <div class="row mb-4">
                        <!-- Dons -->
                        <div class="col-md-6 mb-3">
                            <div class="card shadow h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">🎁 Statistiques Dons</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Total Dons</span>
                                            <strong id="donsTotal"><?= $stats['dons']['total'] ?? 0 ?></strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Dons Nature</span>
                                            <strong id="donsNature"><?= $stats['dons']['nature'] ?? 0 ?></strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Dons Argent</span>
                                            <strong id="donsArgent"><?= $stats['dons']['argent'] ?? 0 ?></strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Montant Total (Ar)</span>
                                            <strong id="donsMontant"><?= number_format($stats['dons']['valeur_totale'] ?? 0, 0, ',', ' ') ?></strong>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Distributions -->
                        <div class="col-md-6 mb-3">
                            <div class="card shadow h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">📦 Statistiques Distributions</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Total Distributions</span>
                                            <strong id="distTotal"><?= $stats['distributions']['total'] ?? 0 ?></strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Confirmées</span>
                                            <strong class="text-success" id="distConfirmees"><?= $stats['distributions']['confirmees'] ?? 0 ?></strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>En attente</span>
                                            <strong class="text-warning" id="distEnAttente"><?= $stats['distributions']['en_attente'] ?? 0 ?></strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Quantité Totale</span>
                                            <strong id="distQuantite"><?= number_format($stats['distributions']['quantite_totale'] ?? 0, 0, ',', ' ') ?></strong>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Achats -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card shadow">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">💰 Statistiques Achats</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <h4 class="fw-bold" id="achatsTotaux"><?= $stats['achats']['total'] ?? 0 ?></h4>
                                            <small class="text-muted">Achats Effectués</small>
                                        </div>
                                        <div class="col-md-3">
                                            <h4 class="fw-bold" id="achatsMontant"><?= number_format($stats['achats']['montant_total'] ?? 0, 0, ',', ' ') ?> Ar</h4>
                                            <small class="text-muted">Montant Total</small>
                                        </div>
                                        <div class="col-md-3">
                                            <h4 class="fw-bold text-danger" id="achatsFrais"><?= number_format($stats['achats']['frais_total'] ?? 0, 0, ',', ' ') ?> Ar</h4>
                                            <small class="text-muted">Frais Appliqués</small>
                                        </div>
                                        <div class="col-md-3">
                                            <h4 class="fw-bold text-success" id="achatsNet"><?= number_format($stats['achats']['montant_net'] ?? 0, 0, ',', ' ') ?> Ar</h4>
                                            <small class="text-muted">Montant Net</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dernière actualisation -->
                    <div class="text-center text-muted mt-4">
                        <small>Dernière actualisation: <span id="derniereActualisation"><?= date('d/m/Y H:i:s') ?></span></small>
                    </div>

                </div>
            </main>
        </div>
    </div>
    
    <?php include __DIR__ . '/../../public/includes/footer.php'; ?>
    
    <script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script<?php if (!empty($nonce)): ?> nonce="<?= htmlspecialchars($nonce) ?>"<?php endif; ?>>
    document.addEventListener('DOMContentLoaded', function() {
        const baseUrl = '<?= htmlspecialchars($base) ?>';
        const btnActualiser = document.getElementById('btnActualiser');
        const spinnerActualiser = document.getElementById('spinnerActualiser');
        const loaderOverlay = document.getElementById('loaderOverlay');

        if (!btnActualiser) return;

        // Bouton ACTUALISER (AJAX)
        btnActualiser.addEventListener('click', function(e) {
            e.preventDefault();
            spinnerActualiser.classList.remove('d-none');
            if (loaderOverlay) loaderOverlay.classList.add('active');
            btnActualiser.disabled = true;

            fetch(baseUrl + '/api/stats', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                spinnerActualiser.classList.add('d-none');
                if (loaderOverlay) loaderOverlay.classList.remove('active');
                btnActualiser.disabled = false;

                if (data.success && data.data) {
                    const stats = data.data;

                    // Mettre à jour indicateurs besoins (EN MONTANT)
                    const montantTotal = stats.besoins?.montant_total || 0;
                    const montantSatisfaits = stats.besoins?.montant_satisfaits || 0;
                    const montantRestants = stats.besoins?.montant_restants || 0;
                    
                    updateElement('besoinsMontantTotal', formatMontant(montantTotal));
                    updateElement('besoinsMontantSatisfaits', formatMontant(montantSatisfaits));
                    updateElement('besoinsMontantRestants', formatMontant(montantRestants));

                    // Mettre à jour barres de progression
                    const pctBesoins = stats.besoins?.pourcentage_montant || stats.besoins?.pourcentage_satisfaits || 0;
                    updateElement('pourcentageBesoins', pctBesoins + '%');
                    updateProgressBar('barreBesoins', pctBesoins);

                    const pctDons = stats.dons?.pourcentage_distribues || 0;
                    updateElement('pourcentageDons', pctDons + '%');
                    updateProgressBar('barreDons', pctDons);

                    const pctDist = stats.distributions?.pourcentage_confirmees || 0;
                    updateElement('pourcentageDistributions', pctDist + '%');
                    updateProgressBar('barreDistributions', pctDist);

                    // Mettre à jour stats dons
                    updateElement('donsTotal', stats.dons?.total || 0);
                    updateElement('donsNature', stats.dons?.nature || 0);
                    updateElement('donsArgent', stats.dons?.argent || 0);
                    updateElement('donsMontant', formatMontant(stats.dons?.valeur_totale || 0));

                    // Mettre à jour stats distributions
                    updateElement('distTotal', stats.distributions?.total || 0);
                    updateElement('distConfirmees', stats.distributions?.confirmees || 0);
                    updateElement('distEnAttente', stats.distributions?.en_attente || 0);
                    updateElement('distQuantite', formatMontant(stats.distributions?.quantite_totale || 0));

                    // Mettre à jour stats achats
                    updateElement('achatsTotaux', stats.achats?.total || 0);
                    updateElement('achatsMontant', formatMontant(stats.achats?.montant_total || 0) + ' Ar');
                    updateElement('achatsFrais', formatMontant(stats.achats?.frais_total || 0) + ' Ar');
                    updateElement('achatsNet', formatMontant(stats.achats?.montant_net || 0) + ' Ar');

                    // Mettre à jour timestamp
                    const now = new Date();
                    updateElement('derniereActualisation', 
                        now.toLocaleDateString('fr-FR') + ' ' + now.toLocaleTimeString('fr-FR'));

                    // Notification succès
                    showAlert('success', 'Données actualisées avec succès !');
                } else {
                    showAlert('danger', data.message || 'Erreur lors de la récupération des données');
                }
            })
            .catch(error => {
                spinnerActualiser.classList.add('d-none');
                if (loaderOverlay) loaderOverlay.classList.remove('active');
                btnActualiser.disabled = false;
                console.error('Erreur:', error);
                showAlert('danger', 'Erreur de connexion: ' + error.message);
            });
        });

        // Fonctions utilitaires
        function updateElement(id, value) {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        }

        function updateProgressBar(id, pct) {
            const bar = document.getElementById(id);
            if (bar) {
                bar.style.width = pct + '%';
                bar.textContent = pct + '%';
                bar.setAttribute('aria-valuenow', pct);
            }
        }

        function formatMontant(val) {
            return new Intl.NumberFormat('fr-FR').format(val || 0);
        }

        function showAlert(type, message) {
            const container = document.querySelector('main .container-fluid');
            if (!container) return;
            
            const existing = container.querySelectorAll('.alert-auto');
            existing.forEach(el => el.remove());
            
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show alert-auto" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            container.insertAdjacentHTML('afterbegin', alertHtml);
            
            setTimeout(() => {
                const alert = container.querySelector('.alert-auto');
                if (alert) alert.remove();
            }, 5000);
        }
    });
    </script>
</body>

</html>
