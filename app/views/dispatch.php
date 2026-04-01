<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
    <meta name="base-url" content="<?= htmlspecialchars($base) ?>">
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <title>Dispatch des Dons - BNGRC</title>
    <style>
        .method-btn {
            min-width: 200px;
            padding: 15px 25px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .method-btn.active {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .method-btn:not(.active) {
            opacity: 0.7;
        }
        .method-description {
            font-size: 0.85rem;
            margin-top: 5px;
        }
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .ratio-badge {
            font-size: 0.75rem;
            vertical-align: middle;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .loading {
            animation: pulse 1.5s infinite;
        }
    </style>
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
                    <!-- Titre -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="fw-bold">📦 Dispatch des Dons</h1>
                    </div>

                    <!-- Messages flash -->
                    <?php if (isset($success) && $success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>✅ Succès!</strong> <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($reinit_success) && $reinit_success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>♻️ Succès!</strong> Les données ont bien été réinitialisées.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>❌ Erreur!</strong> <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Sélecteur de méthode -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">🎯 Choisir la Méthode de Dispatch</h5>
                        </div>
                        <div class="card-body">
                            <div class="row justify-content-center g-3">
                                <!-- Méthode 1: Par Date -->
                                <div class="col-lg-4 col-md-6">
                                    <button type="button" 
                                            class="btn btn-outline-primary method-btn w-100 <?= ($methode ?? 'date') === 'date' ? 'active btn-primary text-white' : '' ?>"
                                            data-methode="date">
                                        <div class="fw-bold">📅 Par Date</div>
                                        <div class="method-description">
                                            Priorité aux demandes les plus anciennes
                                        </div>
                                    </button>
                                </div>
                                
                                <!-- Méthode 2: Par Quantité -->
                                <div class="col-lg-4 col-md-6">
                                    <button type="button" 
                                            class="btn btn-outline-success method-btn w-100 <?= ($methode ?? '') === 'quantite' ? 'active btn-success text-white' : '' ?>"
                                            data-methode="quantite">
                                        <div class="fw-bold">📊 Par Quantité</div>
                                        <div class="method-description">
                                            Priorité aux demandes les plus petites
                                        </div>
                                    </button>
                                </div>
                                
                                <!-- Méthode 3: Par Proportionnalité -->
                                <div class="col-lg-4 col-md-6">
                                    <button type="button" 
                                            class="btn btn-outline-warning method-btn w-100 <?= ($methode ?? '') === 'proportionnalite' ? 'active btn-warning' : '' ?>"
                                            data-methode="proportionnalite">
                                        <div class="fw-bold">⚖️ Par Proportionnalité</div>
                                        <div class="method-description">
                                            Répartition au prorata des demandes
                                        </div>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Description détaillée de la méthode active -->
                            <div class="mt-4 p-3 bg-light rounded" id="methodeDescription">
                                <?php if (($methode ?? 'date') === 'date'): ?>
                                    <h6 class="text-primary"><strong>📅 Méthode par Date</strong></h6>
                                    <p class="mb-0">Les demandes sont traitées par ordre chronologique. La ville qui a déposé sa demande en premier reçoit son don en priorité. Les dons sont également utilisés par ordre d'ancienneté.</p>
                                <?php elseif ($methode === 'quantite'): ?>
                                    <h6 class="text-success"><strong>📊 Méthode par Quantité</strong></h6>
                                    <p class="mb-0">Les demandes les plus petites sont servies en premier. Cette méthode permet de satisfaire un maximum de demandes en privilégiant les besoins modestes.</p>
                                <?php else: ?>
                                    <h6 class="text-warning"><strong>⚖️ Méthode par Proportionnalité (Plus Forts Restes)</strong></h6>
                                    <p class="mb-2">Les dons sont répartis équitablement entre toutes les demandes selon l'algorithme des <strong>plus forts restes</strong> :</p>
                                    <ol class="mb-0 small">
                                        <li>Calcul de la <strong>part théorique</strong> (décimale) pour chaque bénéficiaire</li>
                                        <li>Attribution de la <strong>partie entière</strong> à chacun</li>
                                        <li>Les <strong>restes</strong> (somme des décimales) sont distribués un par un aux bénéficiaires avec les plus grandes parties décimales</li>
                                        <li>Résultat : partie entière <strong>ou</strong> partie entière +1 selon le classement des décimales</li>
                                    </ol>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-warning stat-card">
                                <div class="card-body text-center">
                                    <h6 class="text-warning">Besoins Non Satisfaits</h6>
                                    <h2 class="display-6" id="statBesoins"><?= $stats['total_besoins'] ?? 0 ?></h2>
                                    <small class="text-muted">demandes en attente</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success stat-card">
                                <div class="card-body text-center">
                                    <h6 class="text-success">Quantité Disponible</h6>
                                    <h2 class="display-6" id="statDons"><?= number_format($stats['quantite_totale_demandee'] ?? 0, 0, ',', ' ') ?></h2>
                                    <small class="text-muted">unités demandées</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-primary stat-card">
                                <div class="card-body text-center">
                                    <h6 class="text-primary">Distributions Proposées</h6>
                                    <h2 class="display-6" id="statDistributions"><?= $stats['total_distributions'] ?? 0 ?></h2>
                                    <small class="text-muted">affectations</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info stat-card">
                                <div class="card-body text-center">
                                    <h6 class="text-info">Taux de Satisfaction</h6>
                                    <h2 class="display-6" id="statTaux"><?= $stats['taux_satisfaction'] ?? 0 ?>%</h2>
                                    <small class="text-muted">besoins couverts</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="d-flex gap-3 mb-4 flex-wrap">
                        <button type="button" id="btnSimuler" class="btn btn-primary btn-lg">
                            <span class="spinner-border spinner-border-sm d-none me-2" id="spinnerSimuler"></span>
                            🔄 SIMULER
                        </button>
                        <form method="POST" action="<?= htmlspecialchars($base) ?>/dispatch/valider" id="formValider" class="me-2">
                            <input type="hidden" name="distributions" id="distributionsData" value="">
                            <input type="hidden" name="methode" id="methodeInput" value="<?= htmlspecialchars($methode ?? 'date') ?>">
                            <button type="submit" id="btnValider" class="btn btn-success btn-lg" disabled>
                                <span class="spinner-border spinner-border-sm d-none me-2" id="spinnerValider"></span>
                                ✅ VALIDER LA DISTRIBUTION
                            </button>
                        </form>
                        <form method="POST" action="<?= htmlspecialchars($base) ?>/dispatch/reinitialiser" id="formReinit">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <span class="spinner-border spinner-border-sm d-none me-2" id="spinnerReinit"></span>
                                ♻️ Réinitialiser
                            </button>
                        </form>
                    </div>


 <!-- Tableau des Distributions Proposées -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">📤 Distributions Proposées 
                                <span class="badge bg-light text-primary" id="badgeMethode">
                                    <?= match($methode ?? 'date') {
                                        'date' => '📅 Par Date',
                                        'quantite' => '📊 Par Quantité',
                                        'proportionnalite' => '⚖️ Par Proportionnalité',
                                        default => '📅 Par Date'
                                    } ?>
                                </span>
                            </h5>
                            <span class="badge bg-light text-primary fs-6" id="countDistributions">
                                <?= count($distributions ?? []) ?> distribution(s)
                            </span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($distributions)): ?>
                                <div class="alert alert-info mb-0" id="alertNoDistribution">
                                    <strong>ℹ️ Information</strong><br>
                                    Cliquez sur <strong>SIMULER</strong> pour générer les propositions de distribution selon la méthode choisie.
                                </div>
                            <?php else: ?>
                                <?php if (($methode ?? 'date') === 'proportionnalite'): ?>
                                    <?php 
                                    // Calculer les totaux pour la légende
                                    $totalDemande = array_sum(array_column($distributions, 'besoin_quantite_restante'));
                                    $totalAttribue = array_sum(array_column($distributions, 'quantite_attribuee'));
                                    $ratioGlobal = $totalDemande > 0 ? ($totalAttribue / $totalDemande) * 100 : 0;
                                    $nbBonus = count(array_filter($distributions, fn($d) => !empty($d['a_recu_bonus'])));
                                    ?>
                                    <div class="alert alert-warning mb-3">
                                        <h6 class="alert-heading">📊 Détails du calcul proportionnel</h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>Ratio global:</strong> <?= number_format($ratioGlobal, 1, ',', ' ') ?>%
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Total demandé:</strong> <?= number_format($totalDemande, 0, ',', ' ') ?>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Total attribué:</strong> <?= number_format($totalAttribue, 0, ',', ' ') ?>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Bonus distribués:</strong> <?= $nbBonus ?> (+1)
                                            </div>
                                        </div>
                                        <?php if ($ratioGlobal >= 100): ?>
                                            <hr>
                                            <small class="text-success">✅ Les dons couvrent 100% des besoins. Pas de reste à distribuer (décimales = 0).</small>
                                        <?php else: ?>
                                            <hr>
                                            <small>Les bénéficiaires avec les plus fortes décimales reçoivent +1 unité supplémentaire.</small>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="tableDistributions">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Ville</th>
                                                <th>Produit</th>
                                                <th>Date Besoin</th>
                                                <th>Qté Demandée</th>
                                                <th>Donateur</th>
                                                <th>Qté Attribuée</th>
                                                <?php if (($methode ?? 'date') === 'proportionnalite'): ?>
                                                    <th>Part Théorique</th>
                                                    <th>Décimale</th>
                                                    <th>Bonus</th>
                                                <?php endif; ?>
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
                                                    <td><?= number_format($dist['besoin_quantite_restante'], 0, ',', ' ') ?></td>
                                                    <td><?= htmlspecialchars($dist['donateur_nom']) ?></td>
                                                    <td><strong class="text-success"><?= number_format($dist['quantite_attribuee'], 0, ',', ' ') ?></strong></td>
                                                    <?php if (($methode ?? 'date') === 'proportionnalite'): ?>
                                                        <td><span class="badge bg-secondary"><?= number_format($dist['part_theorique'] ?? 0, 2, ',', ' ') ?></span></td>
                                                        <td><span class="badge bg-info"><?= number_format(($dist['partie_decimale'] ?? 0) * 100, 1, ',', ' ') ?>%</span></td>
                                                        <td>
                                                            <?php if (!empty($dist['a_recu_bonus'])): ?>
                                                                <span class="badge bg-success">+1 ✓</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-light text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endif; ?>
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
                            <?php endif; ?>
                            
                            <!-- Conteneur pour le tableau AJAX -->
                            <div id="distributionsContainer"></div>
                        </div>
                    </div>

                    <!-- Tableau des Besoins non satisfaits -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">📋 Besoins Non Satisfaits</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($besoins)): ?>
                                <div class="alert alert-success mb-0">
                                    <strong>🎉 Excellent !</strong> Tous les besoins ont été satisfaits.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-hover table-sm">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>#</th>
                                                <th>Date Besoin</th>
                                                <th>Ville</th>
                                                <th>Produit</th>
                                                <th>Qté Demandée</th>
                                                <th>Qté Restante</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($besoins as $index => $besoin): ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= date('d/m/Y', strtotime($besoin['dateBesoin'])) ?></td>
                                                    <td><strong><?= htmlspecialchars($besoin['ville_nom']) ?></strong></td>
                                                    <td><?= htmlspecialchars($besoin['produit_nom']) ?></td>
                                                    <td><?= number_format($besoin['quantite'], 0, ',', ' ') ?></td>
                                                    <td><span class="badge bg-warning"><?= number_format($besoin['quantite_restante'], 0, ',', ' ') ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tableau des Dons disponibles -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">🎁 Dons Disponibles</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dons)): ?>
                                <div class="alert alert-warning mb-0">
                                    <strong>⚠️ Attention !</strong> Aucun don disponible pour le moment.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-hover table-sm">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>#</th>
                                                <th>Date Don</th>
                                                <th>Donateur</th>
                                                <th>Type</th>
                                                <th>Produit</th>
                                                <th>Qté Disponible</th>
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
                                                    <td><span class="badge bg-success"><?= number_format($don['quantite_restante'], 0, ',', ' ') ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                   

                    <!-- Résumé -->
                    <?php if (!empty($distributions)): ?>
                    <div class="card shadow mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">📊 Résumé du Dispatch</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <h4 class="text-primary"><?= $stats['total_distributions'] ?? 0 ?></h4>
                                    <small>Distributions</small>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-success"><?= $stats['besoins_completement_satisfaits'] ?? 0 ?></h4>
                                    <small>Besoins satisfaits</small>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-warning"><?= $stats['besoins_partiellement_satisfaits'] ?? 0 ?></h4>
                                    <small>Partiellement</small>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-info"><?= number_format($stats['quantite_totale_attribuee'] ?? 0, 0, ',', ' ') ?></h4>
                                    <small>Unités attribuées</small>
                                </div>
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
    <script<?php if (!empty($nonce)): ?> nonce="<?= htmlspecialchars($nonce) ?>"<?php endif; ?>>
    document.addEventListener('DOMContentLoaded', function() {
        const baseUrl = '<?= htmlspecialchars($base) ?>';
        let methodeActive = '<?= htmlspecialchars($methode ?? 'date') ?>';
        let propositionsSimulees = <?= json_encode($distributions ?? []) ?>;
        
        // Éléments DOM
        const methodeBtns = document.querySelectorAll('.method-btn');
        const btnSimuler = document.getElementById('btnSimuler');
        const btnValider = document.getElementById('btnValider');
        const spinnerSimuler = document.getElementById('spinnerSimuler');
        const spinnerValider = document.getElementById('spinnerValider');
        const distributionsData = document.getElementById('distributionsData');
        const methodeInput = document.getElementById('methodeInput');
        const formValider = document.getElementById('formValider');
        
        // Pré-remplir si distributions existantes
        if (propositionsSimulees.length > 0) {
            distributionsData.value = JSON.stringify(propositionsSimulees);
            btnValider.disabled = false;
        }

        // Gestion des boutons de méthode
        methodeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const nouvelleMethode = this.dataset.methode;
                
                // Mettre à jour l'UI
                methodeBtns.forEach(b => {
                    b.classList.remove('active', 'btn-primary', 'btn-success', 'btn-warning', 'text-white');
                    b.classList.add('btn-outline-' + getColorForMethode(b.dataset.methode));
                });
                
                this.classList.remove('btn-outline-' + getColorForMethode(nouvelleMethode));
                this.classList.add('active', 'btn-' + getColorForMethode(nouvelleMethode));
                if (nouvelleMethode !== 'proportionnalite') {
                    this.classList.add('text-white');
                }
                
                methodeActive = nouvelleMethode;
                methodeInput.value = nouvelleMethode;
                
                // Mettre à jour la description
                updateDescription(nouvelleMethode);
                
                // Réinitialiser les propositions
                propositionsSimulees = [];
                distributionsData.value = '';
                btnValider.disabled = true;
                
                // Lancer automatiquement la simulation
                lancerSimulation();
            });
        });

        function getColorForMethode(methode) {
            return {
                'date': 'primary',
                'quantite': 'success',
                'proportionnalite': 'warning'
            }[methode] || 'primary';
        }

        function updateDescription(methode) {
            const descDiv = document.getElementById('methodeDescription');
            const descriptions = {
                'date': `<h6 class="text-primary"><strong>📅 Méthode par Date</strong></h6>
                         <p class="mb-0">Les demandes sont traitées par ordre chronologique. La ville qui a déposé sa demande en premier reçoit son don en priorité. Les dons sont également utilisés par ordre d'ancienneté.</p>`,
                'quantite': `<h6 class="text-success"><strong>📊 Méthode par Quantité</strong></h6>
                             <p class="mb-0">Les demandes les plus petites sont servies en premier. Cette méthode permet de satisfaire un maximum de demandes en privilégiant les besoins modestes.</p>`,
                'proportionnalite': `<h6 class="text-warning"><strong>⚖️ Méthode par Proportionnalité (Plus Forts Restes)</strong></h6>
                                     <p class="mb-2">Les dons sont répartis équitablement selon l'algorithme des <strong>plus forts restes</strong> :</p>
                                     <ol class="mb-0 small">
                                         <li>Calcul de la <strong>part théorique</strong> (décimale) pour chaque bénéficiaire</li>
                                         <li>Attribution de la <strong>partie entière</strong> à chacun</li>
                                         <li>Les <strong>restes</strong> sont distribués aux bénéficiaires avec les plus grandes parties décimales</li>
                                         <li>Résultat : partie entière <strong>ou</strong> partie entière +1</li>
                                     </ol>`
            };
            descDiv.innerHTML = descriptions[methode] || descriptions['date'];
        }

        // Bouton SIMULER
        btnSimuler.addEventListener('click', function() {
            lancerSimulation();
        });

        function lancerSimulation() {
            spinnerSimuler.classList.remove('d-none');
            btnSimuler.disabled = true;
            btnValider.disabled = true;

            fetch(baseUrl + '/api/dispatch/lancer?methode=' + methodeActive, {
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
                    
                    // Mettre à jour le badge de méthode
                    const badgeMethode = document.getElementById('badgeMethode');
                    const methodeLabels = {
                        'date': '📅 Par Date',
                        'quantite': '📊 Par Quantité',
                        'proportionnalite': '⚖️ Par Proportionnalité'
                    };
                    badgeMethode.textContent = methodeLabels[methodeActive] || methodeLabels['date'];
                    
                    // Mettre à jour le compteur
                    document.getElementById('countDistributions').textContent = propositionsSimulees.length + ' distribution(s)';
                    
                    // Afficher le tableau
                    afficherTableauDistributions(propositionsSimulees, methodeActive);
                    
                    // Mettre à jour les statistiques
                    if (data.stats) {
                        document.getElementById('statBesoins').textContent = data.stats.total_besoins || 0;
                        document.getElementById('statDistributions').textContent = data.stats.total_distributions || 0;
                        document.getElementById('statTaux').textContent = (data.stats.taux_satisfaction || 0) + '%';
                    }

                    // Activer le bouton valider
                    btnValider.disabled = propositionsSimulees.length === 0;
                    
                    showAlert('success', 'Simulation terminée: ' + propositionsSimulees.length + ' distribution(s) proposée(s)');
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
        }

        function afficherTableauDistributions(distributions, methode) {
            const container = document.getElementById('distributionsContainer');
            const alertNoDistrib = document.getElementById('alertNoDistribution');
            const existingTable = document.getElementById('tableDistributions');
            
            if (alertNoDistrib) alertNoDistrib.style.display = 'none';
            if (existingTable) existingTable.style.display = 'none';
            
            if (distributions.length === 0) {
                container.innerHTML = `<div class="alert alert-warning">Aucune distribution proposée.</div>`;
                return;
            }

            const isProportionnel = methode === 'proportionnalite';
            
            // Calculs pour la légende (méthode proportionnelle)
            let legendeHtml = '';
            if (isProportionnel) {
                const totalDemande = distributions.reduce((sum, d) => sum + (d.besoin_quantite_restante || 0), 0);
                const totalAttribue = distributions.reduce((sum, d) => sum + (d.quantite_attribuee || 0), 0);
                const ratioGlobal = totalDemande > 0 ? (totalAttribue / totalDemande) * 100 : 0;
                const nbBonus = distributions.filter(d => d.a_recu_bonus).length;
                
                let messageRatio = '';
                if (ratioGlobal >= 100) {
                    messageRatio = `<hr><small class="text-success">✅ Les dons couvrent 100% des besoins. Pas de reste à distribuer (décimales = 0).</small>`;
                } else {
                    messageRatio = `<hr><small>Les bénéficiaires avec les plus fortes décimales reçoivent +1 unité supplémentaire.</small>`;
                }
                
                legendeHtml = `
                    <div class="alert alert-warning mb-3">
                        <h6 class="alert-heading">📊 Détails du calcul proportionnel</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Ratio global:</strong> ${ratioGlobal.toFixed(1).replace('.', ',')}%
                            </div>
                            <div class="col-md-3">
                                <strong>Total demandé:</strong> ${formatNumber(totalDemande)}
                            </div>
                            <div class="col-md-3">
                                <strong>Total attribué:</strong> ${formatNumber(totalAttribue)}
                            </div>
                            <div class="col-md-3">
                                <strong>Bonus distribués:</strong> ${nbBonus} (+1)
                            </div>
                        </div>
                        ${messageRatio}
                    </div>
                `;
            }
            
            let html = legendeHtml + `
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Ville</th>
                                <th>Produit</th>
                                <th>Date Besoin</th>
                                <th>Qté Demandée</th>
                                <th>Donateur</th>
                                <th>Qté Attribuée</th>
                                ${isProportionnel ? '<th>Part Théorique</th><th>Décimale</th><th>Bonus</th>' : ''}
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            distributions.forEach((dist, index) => {
                const ratio = (dist.quantite_attribuee / dist.besoin_quantite_restante) * 100;
                let statusBadge = '';
                if (ratio >= 100) {
                    statusBadge = '<span class="badge bg-success">Satisfait</span>';
                } else if (ratio >= 50) {
                    statusBadge = '<span class="badge bg-warning text-dark">Partiel</span>';
                } else {
                    statusBadge = '<span class="badge bg-danger">Insuffisant</span>';
                }
                
                // Colonnes pour la méthode proportionnelle
                let proportionnelCols = '';
                if (isProportionnel) {
                    const partTheorique = (dist.part_theorique || 0).toFixed(2).replace('.', ',');
                    const decimale = ((dist.partie_decimale || 0) * 100).toFixed(1).replace('.', ',');
                    const bonus = dist.a_recu_bonus 
                        ? '<span class="badge bg-success">+1 ✓</span>' 
                        : '<span class="badge bg-light text-muted">-</span>';
                    proportionnelCols = `
                        <td><span class="badge bg-secondary">${partTheorique}</span></td>
                        <td><span class="badge bg-info">${decimale}%</span></td>
                        <td>${bonus}</td>
                    `;
                }
                
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td><strong>${escapeHtml(dist.ville_nom || '')}</strong></td>
                        <td>${escapeHtml(dist.produit_nom || '')}</td>
                        <td class="text-muted">${formatDate(dist.dateBesoin)}</td>
                        <td>${formatNumber(dist.besoin_quantite_restante)}</td>
                        <td>${escapeHtml(dist.donateur_nom || '')}</td>
                        <td><strong class="text-success">${formatNumber(dist.quantite_attribuee)}</strong></td>
                        ${proportionnelCols}
                        <td>${statusBadge}</td>
                    </tr>
                `;
            });
            
            html += `</tbody></table></div>`;
            container.innerHTML = html;
        }

        // Formulaire de validation
        formValider.addEventListener('submit', function(e) {
            if (propositionsSimulees.length === 0) {
                e.preventDefault();
                showAlert('warning', 'Veuillez d\'abord lancer une simulation');
                return false;
            }
            
            spinnerValider.classList.remove('d-none');
            btnValider.disabled = true;
            btnSimuler.disabled = true;
        });

        // Fonctions utilitaires
        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function formatNumber(val) {
            return new Intl.NumberFormat('fr-FR').format(val || 0);
        }

        function formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('fr-FR');
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
