<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
    <meta name="base-url" content="<?= htmlspecialchars($base) ?>">
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <title>Simulation de Dispatch Manuel - BNGRC</title>
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
                        <h1 class="fw-bold"> Simulation de Dispatch Automatique</h1>
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

                    <!-- Informations sur le système -->
                    <div class="alert alert-info">
                        <h5 class="alert-heading">Mode de Dispatch Automatique</h5>
                        <p class="mb-0">
                            Le système analyse automatiquement les besoins et les dons disponibles, puis propose un dispatch optimal basé sur :
                            <br><strong>1.</strong> L'ordre chronologique des besoins (les plus anciens en priorité)
                            <br><strong>2.</strong> L'ordre chronologique des dons (les plus anciens utilisés en premier)
                            <br><strong>3.</strong> La correspondance des produits
                            <br><strong>4.</strong> Les quantités disponibles vs requises
                        </p>
                    </div>


                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <h6 class="text-warning">Besoins Non Satisfaits</h6>
                                    <h2 class="display-6" id="statBesoins"><?= $stats['total_besoins'] ?? 0 ?></h2>
                                    <small class="text-muted">En attente d'affectation</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h6 class="text-success">Dons Disponibles</h6>
                                    <h2 class="display-6" id="statDons"><?= $stats['total_dons'] ?? 0 ?></h2>
                                    <small class="text-muted">Non encore affectés</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h6 class="text-primary">Distributions Proposées</h6>
                                    <h2 class="display-6" id="statDistributions"><?= $stats['total_distributions'] ?? 0 ?></h2>
                                    <small class="text-muted">Affectations créées</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h6 class="text-info">Taux de Satisfaction</h6>
                                    <h2 class="display-6" id="statTaux"><?= $stats['taux_satisfaction'] ?? 0 ?>%</h2>
                                    <small class="text-muted">Besoins couverts</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons SIMULER et VALIDER -->
                    <div class="d-flex gap-3 mb-4">
                        <button type="button" id="btnSimuler" class="btn btn-primary btn-lg">
                            <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true" id="spinnerSimuler"></span>
                            🔄 SIMULER
                        </button>
                        <form method="POST" action="<?= htmlspecialchars($base) ?>/simulation/valider" id="formValider" style="display: inline;">
                            <input type="hidden" name="distributions" id="distributionsData" value="">
                            <button type="submit" id="btnValider" class="btn btn-success btn-lg" disabled>
                                <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true" id="spinnerValider"></span>
                                ✓ VALIDER
                            </button>
                        </form>
                    </div>

                    <!-- Tableau 1: Besoins non satisfaits -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-danger text-white">
                            <h4 class="mb-0">1. Besoins Non Satisfaits (par ordre chronologique)</h4>
                        </div>
                        <div class="card-body">
                            <?php if (empty($besoins)): ?>
                                <div class="alert alert-success">
                                    <strong>Excellent !</strong> Tous les besoins ont été satisfaits.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Date Besoin</th>
                                                <th>Ville</th>
                                                <th>Produit</th>
                                                <th>Quantité Demandée</th>
                                                <th>Quantité Restante</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($besoins as $index => $besoin): ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= date('d/m/Y', strtotime($besoin['dateBesoin'])) ?></td>
                                                    <td><strong><?= htmlspecialchars($besoin['ville_nom']) ?></strong></td>
                                                    <td><?= htmlspecialchars($besoin['produit_nom']) ?></td>
                                                    <td><?= htmlspecialchars($besoin['quantite']) ?></td>
                                                    <td>
                                                        <span class="badge bg-warning"><?= htmlspecialchars($besoin['quantite_restante']) ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tableau 2: Dons disponibles -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0">2. Dons Disponibles (par ordre chronologique)</h4>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dons)): ?>
                                <div class="alert alert-warning">
                                    <strong>Attention !</strong> Aucun don disponible pour le moment.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Date Don</th>
                                                <th>Donateur</th>
                                                <th>Type</th>
                                                <th>Produit</th>
                                                <th>Quantité Disponible</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dons as $index => $don): ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= date('d/m/Y', strtotime($don['dateDon'])) ?></td>
                                                    <td><?= htmlspecialchars($don['donateur_nom']) ?></td>
                                                    <td>
                                                        <?php if ($don['type_don'] === 'nature'): ?>
                                                            <span class="badge bg-info">Nature</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning text-dark">Argent</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($don['produit_nom'] ?? 'Don financier') ?></td>
                                                    <td>
                                                        <span class="badge bg-success"><?= number_format($don['quantite_restante'], 0, ',', ' ') ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tableau 3: Distributions proposées automatiquement -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">3. Distributions Proposées Automatiquement</h4>
                            <?php if (!empty($distributions)): ?>
                                <form method="POST" action="<?= htmlspecialchars($base) ?>/simulation/confirmer" style="display: inline;" id="formConfirmer">
                                    <button type="submit" class="btn btn-success btn-lg" id="btnConfirmer">
                                        ✓ Confirmer et Enregistrer (<?= count($distributions) ?>)
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if (empty($distributions)): ?>
                                <div class="alert alert-warning">
                                    <strong>Aucune distribution proposée.</strong>
                                    <br>Raisons possibles :
                                    <ul class="mb-0 mt-2">
                                        <li>Aucun besoin non satisfait</li>
                                        <li>Aucun don disponible</li>
                                        <li>Aucune correspondance produit entre besoins et dons</li>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mb-3">
                                    <strong>ℹ️ Information :</strong> Le système a automatiquement matché <strong><?= count($distributions) ?></strong> distribution(s) 
                                    en fonction de l'ordre chronologique et de la disponibilité des produits.
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Ville</th>
                                                <th>Produit</th>
                                                <th>Besoin (Date)</th>
                                                <th>Qté Demandée</th>
                                                <th>Donateur</th>
                                                <th>Don (Date)</th>
                                                <th>Qté Disponible</th>
                                                <th>Qté Attribuée</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($distributions as $index => $dist): ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><strong><?= htmlspecialchars($dist['ville_nom']) ?></strong></td>
                                                    <td><?= htmlspecialchars($dist['produit_nom']) ?></td>
                                                    <td class="text-muted"><?= date('d/m/Y', strtotime($dist['dateBesoin'])) ?></td>
                                                    <td><?= htmlspecialchars($dist['besoin_quantite_demandee']) ?></td>
                                                    <td><?= htmlspecialchars($dist['donateur_nom']) ?></td>
                                                    <td class="text-muted"><?= date('d/m/Y', strtotime($dist['dateDon'])) ?></td>
                                                    <td><?= htmlspecialchars($dist['don_quantite_disponible']) ?></td>
                                                    <td>
                                                        <strong class="text-success"><?= htmlspecialchars($dist['quantite_attribuee']) ?></strong>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $ratio = ($dist['quantite_attribuee'] / $dist['besoin_quantite_restante']) * 100;
                                                        if ($ratio >= 100) {
                                                            echo '<span class="badge bg-success">Satisfait</span>';
                                                        } elseif ($ratio >= 50) {
                                                            echo '<span class="badge bg-warning text-dark">Partiel</span>';
                                                        } else {
                                                            echo '<span class="badge bg-danger">Insuffisant</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Résumé des distributions -->
                                <div class="alert alert-success mt-3">
                                    <h5 class="alert-heading">📊 Résumé du Dispatch</h5>
                                    <hr>
                                    <p class="mb-0">
                                        <strong><?= count($distributions) ?></strong> distribution(s) proposée(s) automatiquement.
                                        <br>Cliquez sur <strong>"Confirmer et Enregistrer"</strong> pour les enregistrer définitivement en base de données.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tableau 4: Propositions Nature (AJAX) -->
                    <div class="card shadow mb-4" id="cardPropositionsNature" style="display: none;">
                        <div class="card-header bg-info text-white">
                            <h4 class="mb-0">📦 Propositions Nature (Dons en nature)</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="tablePropositionsNature">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Ville</th>
                                            <th>Produit</th>
                                            <th>Quantité</th>
                                            <th>Donateur</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Rempli dynamiquement via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau 5: Propositions Achats (AJAX) -->
                    <div class="card shadow mb-4" id="cardPropositionsAchats" style="display: none;">
                        <div class="card-header bg-warning text-dark">
                            <h4 class="mb-0">💰 Propositions Achats (Dons financiers)</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="tablePropositionsAchats">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Ville</th>
                                            <th>Produit</th>
                                            <th>Quantité</th>
                                            <th>Coût estimé</th>
                                            <th>Frais</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Rempli dynamiquement via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Résumé Simulation (AJAX) -->
                    <div class="card shadow mb-4" id="cardResume" style="display: none;">
                        <div class="card-header bg-dark text-white">
                            <h4 class="mb-0">📊 Résumé de la Simulation</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h5 class="text-primary" id="resumeNature">0</h5>
                                        <small class="text-muted">Distributions Nature</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h5 class="text-warning" id="resumeAchats">0</h5>
                                        <small class="text-muted">Achats Proposés</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h5 class="text-success" id="resumeTotal">0</h5>
                                        <small class="text-muted">Total Distributions</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h5 class="text-info" id="resumeTaux">0%</h5>
                                        <small class="text-muted">Taux Satisfaction</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . $base . '/includes/footer.php'; ?>
    
    <script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script<?php if (!empty($nonce)): ?> nonce="<?= htmlspecialchars($nonce) ?>"<?php endif; ?>>
    document.addEventListener('DOMContentLoaded', function() {
        const baseUrl = '<?= htmlspecialchars($base) ?>';
        const btnSimuler = document.getElementById('btnSimuler');
        const btnValider = document.getElementById('btnValider');
        const spinnerSimuler = document.getElementById('spinnerSimuler');
        const distributionsData = document.getElementById('distributionsData');
        
        // Initialiser avec les distributions déjà calculées côté serveur (si disponibles)
        let propositionsSimulees = <?= json_encode($distributions ?? []) ?>;
        
        // Pré-remplir le champ caché si des distributions existent
        if (propositionsSimulees.length > 0) {
            distributionsData.value = JSON.stringify(propositionsSimulees);
            btnValider.disabled = false;
        }

        if (!btnSimuler) return;

        // Bouton SIMULER
        btnSimuler.addEventListener('click', function(e) {
            e.preventDefault();
            spinnerSimuler.classList.remove('d-none');
            btnSimuler.disabled = true;
            btnValider.disabled = true;

            fetch(baseUrl + '/api/simulation/lancer', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                spinnerSimuler.classList.add('d-none');
                btnSimuler.disabled = false;

                if (data.success && data.distributions) {
                    propositionsSimulees = data.distributions;
                    distributionsData.value = JSON.stringify(propositionsSimulees);
                    
                    // Séparer nature et achats
                    const nature = propositionsSimulees.filter(d => d.type === 'nature' || !d.type);
                    const achats = propositionsSimulees.filter(d => d.type === 'achat');

                    // Afficher tableau nature
                    const cardNature = document.getElementById('cardPropositionsNature');
                    if (nature.length > 0 && cardNature) {
                        cardNature.style.display = 'block';
                        const tbody = document.querySelector('#tablePropositionsNature tbody');
                        if (tbody) {
                            tbody.innerHTML = nature.map((d, i) => `
                                <tr>
                                    <td>${i + 1}</td>
                                    <td><strong>${escapeHtml(d.ville_nom || '')}</strong></td>
                                    <td>${escapeHtml(d.produit_nom || '')}</td>
                                    <td><span class="badge bg-success">${d.quantite_attribuee || d.quantite || 0}</span></td>
                                    <td>${escapeHtml(d.donateur_nom || '')}</td>
                                    <td><span class="badge bg-info">Nature</span></td>
                                </tr>
                            `).join('');
                        }
                    }

                    // Afficher tableau achats
                    const cardAchats = document.getElementById('cardPropositionsAchats');
                    if (achats.length > 0 && cardAchats) {
                        cardAchats.style.display = 'block';
                        const tbody = document.querySelector('#tablePropositionsAchats tbody');
                        if (tbody) {
                            tbody.innerHTML = achats.map((d, i) => `
                                <tr>
                                    <td>${i + 1}</td>
                                    <td><strong>${escapeHtml(d.ville_nom || '')}</strong></td>
                                    <td>${escapeHtml(d.produit_nom || '')}</td>
                                    <td>${d.quantite || 0}</td>
                                    <td>${formatMontant(d.montant || 0)} Ar</td>
                                    <td>${formatMontant(d.frais || 0)} Ar</td>
                                    <td><strong>${formatMontant(d.total || 0)} Ar</strong></td>
                                </tr>
                            `).join('');
                        }
                    }

                    // Afficher résumé
                    const cardResume = document.getElementById('cardResume');
                    if (cardResume) {
                        cardResume.style.display = 'block';
                        document.getElementById('resumeNature').textContent = nature.length;
                        document.getElementById('resumeAchats').textContent = achats.length;
                        document.getElementById('resumeTotal').textContent = propositionsSimulees.length;
                        document.getElementById('resumeTaux').textContent = (data.stats?.taux_satisfaction || 0) + '%';
                    }

                    // Mettre à jour les statistiques en haut de page
                    if (data.stats) {
                        const statBesoins = document.getElementById('statBesoins');
                        const statDons = document.getElementById('statDons');
                        const statDistributions = document.getElementById('statDistributions');
                        const statTaux = document.getElementById('statTaux');
                        
                        if (statBesoins) statBesoins.textContent = data.stats.total_besoins || 0;
                        if (statDons) statDons.textContent = data.stats.total_dons || 0;
                        if (statDistributions) statDistributions.textContent = data.stats.total_distributions || 0;
                        if (statTaux) statTaux.textContent = (data.stats.taux_satisfaction || 0) + '%';
                    }

                    // Activer bouton valider
                    if (propositionsSimulees.length > 0) {
                        btnValider.disabled = false;
                    } else {
                        btnValider.disabled = true;
                    }

                    showAlert('success', 'Simulation terminée: ' + propositionsSimulees.length + ' proposition(s)');
                } else {
                    showAlert('warning', data.message || 'Aucune proposition générée');
                    btnValider.disabled = true;
                }
            })
            .catch(error => {
                spinnerSimuler.classList.add('d-none');
                btnSimuler.disabled = false;
                console.error('Erreur:', error);
                showAlert('danger', 'Erreur lors de la simulation: ' + error.message);
            });
        });

        // Confirmation avant validation
        const formValider = document.getElementById('formValider');
        const spinnerValider = document.getElementById('spinnerValider');
        
        if (formValider) {
            formValider.addEventListener('submit', function(e) {
                // Vérifier qu'une simulation a été lancée
                if (propositionsSimulees.length === 0) {
                    e.preventDefault();
                    showAlert('warning', 'Veuillez d\'abord lancer une simulation');
                    return false;
                }
                
                // Afficher le spinner et désactiver les boutons
                spinnerValider.classList.remove('d-none');
                btnValider.disabled = true;
                btnSimuler.disabled = true;
                
                // Le formulaire sera soumis normalement via POST
                // Les données sont déjà dans distributionsData.value
                return true;
            });
        }

        // Le formulaire "Confirmer et Enregistrer" soumet directement sans popup
        // La validation se fait côté serveur

        // Fonctions utilitaires
        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function formatMontant(val) {
            return new Intl.NumberFormat('fr-FR').format(val || 0);
        }

        function showAlert(type, message) {
            const container = document.querySelector('main .container-fluid');
            if (!container) return;
            
            // Supprimer les alertes existantes
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