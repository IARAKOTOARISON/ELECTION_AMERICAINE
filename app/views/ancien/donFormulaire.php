<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <title>Saisie des Dons - BNGRC</title>
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
                        <h1 class="fw-bold">Enregistrement des Dons</h1>
                        <a href="<?= htmlspecialchars($base) ?>/dons/liste" class="btn btn-secondary">
                            Voir la liste des dons
                        </a>
                    </div>

                    <?php if (isset($success) && $success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Succès !</strong> <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Erreur !</strong> <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Formulaire de saisie -->
                    <div class="row justify-content-center">
                        <div class="col-lg-10">
                            <div class="card shadow">
                                <div class="card-header bg-success text-white">
                                    <h4 class="mb-0">Enregistrer un nouveau don</h4>
                                </div>
                                <div class="card-body">
                                    <form id="formDon" method="POST" action="<?= htmlspecialchars($base) ?>/dons/ajouter">
                                        
                                        <!-- Type de don -->
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">
                                                <span class="text-danger">*</span> Type de don
                                            </label>
                                            <div class="d-flex gap-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="typeDon" id="donNature" value="nature" checked>
                                                    <label class="form-check-label fw-bold" for="donNature">
                                                        Don en nature (produits)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="typeDon" id="donArgent" value="argent">
                                                    <label class="form-check-label fw-bold" for="donArgent">
                                                        Don en argent
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Section Don en Nature -->
                                        <div id="sectionNature" class="border rounded p-4 mb-4 bg-light">
                                            <h5 class="text-success mb-3">Don en Nature</h5>
                                            
                                            <!-- Nom du donateur -->
                                            <div class="mb-3">
                                                <label for="donateurNature" class="form-label fw-bold">
                                                    <span class="text-danger">*</span> Nom du donateur / Organisation
                                                </label>
                                                <input type="text" class="form-control form-control-lg" id="donateurNature" 
                                                       name="donateurNature" placeholder="Nom complet ou organisation">
                                            </div>

                                            <!-- Sélection du produit -->
                                            <div class="mb-3">
                                                <label for="produitNature" class="form-label fw-bold">
                                                    <span class="text-danger">*</span> Type de produit
                                                </label>
                                                <select class="form-select form-select-lg" id="produitNature" name="produitNature">
                                                    <option value="" selected disabled>-- Sélectionnez un produit --</option>
                                                    <?php if (!empty($produits) && is_array($produits)): ?>
                                                        <?php foreach ($produits as $p): ?>
                                                            <?php
                                                                $pid = $p['id'] ?? $p['ID'] ?? null;
                                                                $pnom = $p['nom'] ?? $p['NOM'] ?? $p['name'] ?? null;
                                                            ?>
                                                            <?php if ($pid !== null && $pnom !== null): ?>
                                                                <option value="<?= htmlspecialchars($pid) ?>"><?= htmlspecialchars($pnom) ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <!-- Fallback statique -->
                                                        <option value="1">Riz</option>
                                                        <option value="2">Huile</option>
                                                        <option value="3">Eau potable</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>

                                            <!-- Quantité -->
                                            <div class="mb-3">
                                                <label for="quantiteNature" class="form-label fw-bold">
                                                    <span class="text-danger">*</span> Quantité
                                                </label>
                                                <input type="number" class="form-control form-control-lg" id="quantiteNature" 
                                                       name="quantiteNature" min="1" step="1" placeholder="Exemple: 100">
                                            </div>
                                        </div>

                                        <!-- Section Don en Argent -->
                                        <div id="sectionArgent" class="border rounded p-4 mb-4 bg-light" style="display: none;">
                                            <h5 class="text-success mb-3">Don en Argent</h5>
                                            
                                            <!-- Nom du donateur -->
                                            <div class="mb-3">
                                                <label for="donateurArgent" class="form-label fw-bold">
                                                    <span class="text-danger">*</span> Nom du donateur / Organisation
                                                </label>
                                                <input type="text" class="form-control form-control-lg" id="donateurArgent" 
                                                       name="donateurArgent" placeholder="Nom complet ou organisation">
                                            </div>

                                            <!-- Montant -->
                                            <div class="mb-3">
                                                <label for="montant" class="form-label fw-bold">
                                                    <span class="text-danger">*</span> Montant
                                                </label>
                                                <div class="input-group input-group-lg">
                                                    <input type="number" class="form-control" id="montant" 
                                                           name="montant" min="0" step="0.01" placeholder="0.00">
                                                    <span class="input-group-text">Ar</span>
                                                </div>
                                                <div class="form-text">Montant en Ariary (Ar)</div>
                                            </div>
                                        </div>

                                        <!-- Date du don -->
                                        <div class="mb-3">
                                            <label for="dateDon" class="form-label fw-bold">
                                                <span class="text-danger">*</span> Date du don
                                            </label>
                                            <input type="date" class="form-control form-control-lg" id="dateDon" 
                                                   name="dateDon" required value="<?php echo date('Y-m-d'); ?>">
                                            <div class="form-text">Date de réception du don (par défaut: aujourd'hui)</div>
                                        </div>

                                        <!-- Ville destinataire (optionnel) -->
                                        <div class="mb-3">
                                            <label for="villeDestinataire" class="form-label fw-bold">
                                                Ville destinataire (optionnel)
                                            </label>
                                            <select class="form-select form-select-lg" id="villeDestinataire" name="villeDestinataire">
                                                <option value="" selected>-- Non spécifié (à attribuer plus tard) --</option>
                                                <?php if (!empty($villes) && is_array($villes)): ?>
                                                    <?php foreach ($villes as $v): ?>
                                                        <?php
                                                            $vid = $v['id'] ?? $v['ID'] ?? null;
                                                            $vnom = $v['nom'] ?? $v['NOM'] ?? $v['name'] ?? null;
                                                        ?>
                                                        <?php if ($vid !== null && $vnom !== null): ?>
                                                            <option value="<?= htmlspecialchars($vid) ?>"><?= htmlspecialchars($vnom) ?></option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <div class="form-text">Si le don est destiné à une ville spécifique</div>
                                        </div>

                                        <!-- Notes/Commentaires -->
                                        <div class="mb-4" style="display:none;">
                                            <label for="notesDon" class="form-label fw-bold">
                                                Notes additionnelles (optionnel)
                                            </label>
                                            <textarea class="form-control" id="notesDon" name="notesDon" rows="3" 
                                                      placeholder="Informations supplémentaires sur le don..."></textarea>
                                        </div>

                                        <!-- Boutons -->
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-success btn-lg px-5">
                                                <strong>Enregistrer le don</strong>
                                            </button>
                                            <button type="reset" class="btn btn-secondary btn-lg">
                                                Réinitialiser
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <?php include __DIR__ . '/../../public/includes/footer.php'; ?>
    
    <script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script<?php if (!empty($nonce)): ?> nonce="<?= htmlspecialchars($nonce) ?>"<?php endif; ?>>
        // Gestion du type de don
        const donNature = document.getElementById('donNature');
        const donArgent = document.getElementById('donArgent');
        const sectionNature = document.getElementById('sectionNature');
        const sectionArgent = document.getElementById('sectionArgent');

        donNature.addEventListener('change', function() {
            if (this.checked) {
                sectionNature.style.display = 'block';
                sectionArgent.style.display = 'none';
                
                // Activer les champs de la section nature
                document.getElementById('donateurNature').required = true;
                document.getElementById('produitNature').required = true;
                document.getElementById('quantiteNature').required = true;
                
                // Désactiver les champs de la section argent
                document.getElementById('donateurArgent').required = false;
                document.getElementById('montant').required = false;
            }
        });

        donArgent.addEventListener('change', function() {
            if (this.checked) {
                sectionNature.style.display = 'none';
                sectionArgent.style.display = 'block';
                
                // Désactiver les champs de la section nature
                document.getElementById('donateurNature').required = false;
                document.getElementById('produitNature').required = false;
                document.getElementById('quantiteNature').required = false;
                
                // Activer les champs de la section argent
                document.getElementById('donateurArgent').required = true;
                document.getElementById('montant').required = true;
            }
        });

        // Script pour afficher l'unité correspondant au produit sélectionné
        document.getElementById('produitNature').addEventListener('change', function() {
            const quantiteInput = document.getElementById('quantiteNature');
            const produit = this.value;
            
            const unites = {
                'riz': 'kg',
                'huile': 'litres',
                'eau': 'litres',
                'tole': 'unités',
                'bois': 'm³',
                'ciment': 'sacs',
                'conserves': 'unités',
                'tente': 'unités',
                'couverture': 'unités',
                'medicaments': 'kits',
                'kit_hygiene': 'unités',
                'vetements': 'lots',
                'lait': 'kg',
                'sucre': 'kg',
                'clous': 'kg',
                'lampe': 'unités',
                'jerrycan': 'unités',
                'savon': 'unités',
                'desinfectant': 'litres',
                'masque': 'boîtes'
            };
            
            if (unites[produit]) {
                quantiteInput.placeholder = `Exemple: 100 ${unites[produit]}`;
            }
        });

        document.getElementById('formDon').addEventListener('submit', function(e) {
           
        });
    </script>

    <?php include $_SERVER['DOCUMENT_ROOT'] . $base . '/includes/footer.php'; ?>
</body>

</html>
