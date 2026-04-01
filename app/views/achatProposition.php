<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
    <meta name="base-url" content="<?= htmlspecialchars($base) ?>">
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <title>Achats Manuels - BNGRC</title>
    <style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .shake-animation {
            animation: shake 0.3s ease-in-out;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/../../public/includes/header.php'; ?>

    <div class="container-fluid flex-grow-1">
        <div class="row h-100">
            <nav class="col-md-3 col-lg-2 bg-dark text-white p-3">
                <?php include __DIR__ . '/../../public/includes/menu.php'; ?>
            </nav>

            <main class="col-md-9 col-lg-10 p-4">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="fw-bold">💰 Achats Manuels (Conversion Argent → Matériel)</h1>
                        <a href="<?= htmlspecialchars($base) ?>/achats" class="btn btn-secondary">
                            ← Retour Liste Achats
                        </a>
                    </div>

                    <?php if (isset($success) && $success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Résumé des dons en argent disponibles -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h6 class="text-uppercase">Dons en Argent Disponibles</h6>
                                    <h2 class="fw-bold" id="totalArgentDisponible"><?= number_format($totalDonsArgent ?? 0, 0, ',', ' ') ?> Ar</h2>
                                    <small><?= count($donsArgent ?? []) ?> don(s) financier(s)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-dark">
                                <div class="card-body text-center">
                                    <h6 class="text-uppercase">Frais d'Achat</h6>
                                    <h2 class="fw-bold"><?= number_format($fraisPourcent ?? 10, 1) ?> %</h2>
                                    <small>Frais appliqués à chaque achat</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h6 class="text-uppercase">Besoins Non Couverts</h6>
                                    <h2 class="fw-bold"><?= count($besoinsRestants ?? []) ?></h2>
                                    <small>En attente de matériel</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des dons en argent -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">💵 Dons Financiers Disponibles</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($donsArgent)): ?>
                                <div class="alert alert-warning mb-0">
                                    <strong>Attention !</strong> Aucun don financier disponible pour effectuer des achats.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Date</th>
                                                <th>Donateur</th>
                                                <th>Montant Initial</th>
                                                <th>Montant Restant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($donsArgent as $don): ?>
                                                <tr>
                                                    <td><?= (int)($don['id'] ?? 0) ?></td>
                                                    <td><?= date('d/m/Y', strtotime($don['dateDon'] ?? '')) ?></td>
                                                    <td><?= htmlspecialchars($don['donateur_nom'] ?? 'Anonyme') ?></td>
                                                    <td><?= number_format($don['montant'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                    <td><span class="badge bg-success"><?= number_format($don['montant_restant'] ?? 0, 0, ',', ' ') ?> Ar</span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Besoins à acheter (lecture seule) -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">🛒 Besoins à Acheter (référence)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($besoinsRestants)): ?>
                                <div class="alert alert-info mb-0">
                                    Tous les besoins sont satisfaits ! Aucun besoin en attente.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date Besoin</th>
                                                <th>Ville</th>
                                                <th>Produit</th>
                                                <th>Qté Restante</th>
                                                <th>Prix Unit.</th>
                                                <th>Coût Estimé</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($besoinsRestants as $b): 
                                                $cout = ($b['quantite_restante'] ?? 0) * ($b['prixUnitaire'] ?? 0);
                                            ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($b['dateBesoin'] ?? '')) ?></td>
                                                    <td><strong><?= htmlspecialchars($b['ville_nom'] ?? '') ?></strong></td>
                                                    <td><?= htmlspecialchars($b['produit_nom'] ?? '') ?></td>
                                                    <td><span class="badge bg-warning"><?= number_format($b['quantite_restante'] ?? 0, 0, ',', ' ') ?></span></td>
                                                    <td><?= number_format($b['prixUnitaire'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                    <td><?= number_format($cout, 0, ',', ' ') ?> Ar</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- SECTION ACHAT MANUEL -->
                    <div class="card shadow mb-4 border-primary">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">🛍️ Effectuer un Achat Manuel</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($donsArgent)): ?>
                                <div class="alert alert-danger mb-0">
                                    <strong>Impossible !</strong> Aucun don financier disponible pour effectuer un achat.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <strong>ℹ️ Mode manuel :</strong> Sélectionnez un don financier, un produit à acheter et la quantité souhaitée.
                                    L'argent sera converti en matériel (nouveau don en nature).
                                </div>
                                
                                <form method="POST" action="<?= htmlspecialchars($base) ?>/achats/manuel/valider" id="formAchatManuel">
                                    <div class="row g-3">
                                        <!-- Sélection du don financier -->
                                        <div class="col-md-4">
                                            <label for="selectDon" class="form-label fw-bold">💵 Don Financier à utiliser</label>
                                            <select class="form-select" id="selectDon" name="id_don" required>
                                                <option value="">-- Sélectionner un don --</option>
                                                <?php foreach ($donsArgent as $don): ?>
                                                    <option value="<?= (int)$don['id'] ?>" 
                                                            data-montant="<?= (float)$don['montant_restant'] ?>">
                                                        <?= htmlspecialchars($don['donateur_nom'] ?? 'Anonyme') ?> 
                                                        - <?= number_format($don['montant_restant'], 0, ',', ' ') ?> Ar
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted">Montant disponible: <span id="montantDonSelectionne">0</span> Ar</small>
                                        </div>
                                        
                                        <!-- Sélection du produit -->
                                        <div class="col-md-4">
                                            <label for="selectProduit" class="form-label fw-bold">📦 Produit à acheter</label>
                                            <select class="form-select" id="selectProduit" name="id_produit" required>
                                                <option value="">-- Sélectionner un produit --</option>
                                                <?php foreach ($produits as $p): ?>
                                                    <option value="<?= (int)$p['id'] ?>" 
                                                            data-prix="<?= (float)$p['prixUnitaire'] ?>">
                                                        <?= htmlspecialchars($p['nom']) ?> 
                                                        (<?= number_format($p['prixUnitaire'], 0, ',', ' ') ?> Ar/unité)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted">Prix unitaire: <span id="prixProduitSelectionne">0</span> Ar</small>
                                        </div>
                                        
                                        <!-- Quantité -->
                                        <div class="col-md-4">
                                            <label for="inputQuantite" class="form-label fw-bold">🔢 Quantité</label>
                                            <input type="number" class="form-control" id="inputQuantite" name="quantite" 
                                                   min="1" step="1" value="1" required>
                                            <small class="text-muted">Max possible: <span id="quantiteMax">0</span> unités</small>
                                        </div>
                                    </div>
                                    
                                    <!-- Résumé du coût -->
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="alert alert-secondary" id="resumeAchat">
                                                <div class="row text-center">
                                                    <div class="col-md-3">
                                                        <strong>Coût produits:</strong><br>
                                                        <span class="fs-5" id="coutProduits">0</span> Ar
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>Frais (<?= number_format($fraisPourcent ?? 10, 0) ?>%):</strong><br>
                                                        <span class="fs-5" id="montantFrais">0</span> Ar
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>Total à débiter:</strong><br>
                                                        <span class="fs-4 fw-bold text-danger" id="totalDebiter">0</span> Ar
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>Reste après achat:</strong><br>
                                                        <span class="fs-4 fw-bold" id="resteApres">0</span> Ar
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Bouton valider -->
                                    <div class="row mt-3">
                                        <div class="col-12 text-center">
                                            <button type="submit" class="btn btn-success btn-lg" id="btnValiderAchat" disabled>
                                                ✅ Valider l'Achat
                                            </button>
                                            <p class="text-muted mt-2" id="messageValidation">
                                                Sélectionnez un don, un produit et une quantité valide.
                                            </p>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Historique des achats récents -->
                    <?php if (!empty($achatsRecents)): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">📋 Achats Récents</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Produit</th>
                                            <th>Quantité</th>
                                            <th>Montant</th>
                                            <th>Frais</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($achatsRecents as $achat): ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($achat['date_achat'] ?? '')) ?></td>
                                                <td><?= htmlspecialchars($achat['produit_nom'] ?? '') ?></td>
                                                <td><?= number_format($achat['quantite'] ?? 0, 0, ',', ' ') ?></td>
                                                <td><?= number_format($achat['montant_total'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                <td><?= number_format($achat['frais_appliques'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                                <td><strong><?= number_format(($achat['montant_total'] ?? 0) + ($achat['frais_appliques'] ?? 0), 0, ',', ' ') ?> Ar</strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <?php include __DIR__ . '/../../public/includes/footer.php'; ?>
    <script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script<?php if (!empty($nonce)): ?> nonce="<?= htmlspecialchars($nonce) ?>"<?php endif; ?>>
    document.addEventListener('DOMContentLoaded', function() {
        const fraisPourcent = <?= json_encode($fraisPourcent ?? 10) ?>;
        
        const selectDon = document.getElementById('selectDon');
        const selectProduit = document.getElementById('selectProduit');
        const inputQuantite = document.getElementById('inputQuantite');
        const btnValider = document.getElementById('btnValiderAchat');
        
        const montantDonSelectionne = document.getElementById('montantDonSelectionne');
        const prixProduitSelectionne = document.getElementById('prixProduitSelectionne');
        const quantiteMax = document.getElementById('quantiteMax');
        const coutProduits = document.getElementById('coutProduits');
        const montantFrais = document.getElementById('montantFrais');
        const totalDebiter = document.getElementById('totalDebiter');
        const resteApres = document.getElementById('resteApres');
        const messageValidation = document.getElementById('messageValidation');
        
        function getMontantDon() {
            const option = selectDon?.selectedOptions[0];
            return option ? parseFloat(option.dataset.montant) || 0 : 0;
        }
        
        function getPrixProduit() {
            const option = selectProduit?.selectedOptions[0];
            return option ? parseFloat(option.dataset.prix) || 0 : 0;
        }
        
        function calculer() {
            if (!selectDon || !selectProduit || !inputQuantite) return;
            
            const montant = getMontantDon();
            const prix = getPrixProduit();
            const qte = parseInt(inputQuantite.value) || 0;
            
            // Afficher infos sélection
            if (montantDonSelectionne) montantDonSelectionne.textContent = montant.toLocaleString('fr-FR');
            if (prixProduitSelectionne) prixProduitSelectionne.textContent = prix.toLocaleString('fr-FR');
            
            // Calculer le coût
            const cout = prix * qte;
            const frais = cout * (fraisPourcent / 100);
            const total = cout + frais;
            const reste = montant - total;
            
            // Calculer quantité max possible
            const maxQte = prix > 0 ? Math.floor(montant / (prix * (1 + fraisPourcent / 100))) : 0;
            if (quantiteMax) quantiteMax.textContent = maxQte.toLocaleString('fr-FR');
            
            // Afficher résumé
            if (coutProduits) coutProduits.textContent = cout.toLocaleString('fr-FR');
            if (montantFrais) montantFrais.textContent = Math.round(frais).toLocaleString('fr-FR');
            if (totalDebiter) totalDebiter.textContent = Math.round(total).toLocaleString('fr-FR');
            if (resteApres) {
                resteApres.textContent = Math.round(reste).toLocaleString('fr-FR');
                resteApres.className = reste >= 0 ? 'fs-4 fw-bold text-success' : 'fs-4 fw-bold text-danger';
            }
            
            // Validation
            const isValid = selectDon.value && selectProduit.value && qte > 0 && reste >= 0;
            if (btnValider) btnValider.disabled = !isValid;
            
            if (messageValidation) {
                if (!selectDon.value) {
                    messageValidation.textContent = 'Sélectionnez un don financier.';
                    messageValidation.className = 'text-muted mt-2';
                } else if (!selectProduit.value) {
                    messageValidation.textContent = 'Sélectionnez un produit à acheter.';
                    messageValidation.className = 'text-muted mt-2';
                } else if (qte <= 0) {
                    messageValidation.textContent = 'Entrez une quantité valide.';
                    messageValidation.className = 'text-muted mt-2';
                } else if (reste < 0) {
                    messageValidation.textContent = '❌ Budget insuffisant ! Réduisez la quantité.';
                    messageValidation.className = 'text-danger mt-2 fw-bold';
                } else {
                    messageValidation.textContent = '✅ Achat valide. Cliquez pour confirmer.';
                    messageValidation.className = 'text-success mt-2 fw-bold';
                }
            }
        }
        
        // Événements
        if (selectDon) selectDon.addEventListener('change', calculer);
        if (selectProduit) selectProduit.addEventListener('change', calculer);
        if (inputQuantite) inputQuantite.addEventListener('input', calculer);
        
        // Formulaire
        const form = document.getElementById('formAchatManuel');
        const messageValidation = document.getElementById('messageValidation');
        if (form) {
            form.addEventListener('submit', function(e) {
                const montant = getMontantDon();
                const prix = getPrixProduit();
                const qte = parseInt(inputQuantite.value) || 0;
                const total = prix * qte * (1 + fraisPourcent / 100);
                
                if (total > montant) {
                    e.preventDefault();
                    // Afficher erreur dans le message de validation (pas de popup)
                    if (messageValidation) {
                        messageValidation.innerHTML = '<span class="text-danger fw-bold">⚠️ Budget insuffisant pour cet achat !</span>';
                        messageValidation.classList.add('shake-animation');
                        setTimeout(() => messageValidation.classList.remove('shake-animation'), 500);
                    }
                    return false;
                }
                
                // Soumission directe sans popup de confirmation
            });
        }
        
        // Init
        calculer();
    });
    </script>
</body>

</html>
