<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
    <link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/style.css" rel="stylesheet">
    <title>Saisie des Besoins - BNGRC</title>
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
                        <h1 class="fw-bold">Saisie des Besoins par Ville</h1>
                        <a href="<?= htmlspecialchars($base) ?>/besoins/liste" class="btn btn-secondary">
                            Voir la liste des besoins
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
                                <div class="card-header bg-primary text-white">
                                    <h4 class="mb-0">Enregistrer un nouveau besoin</h4>
                                </div>
                                <div class="card-body">
                                    <form id="formBesoin" method="POST" action="<?= htmlspecialchars($base) ?>/besoins/ajouter">
                                        
                                        <!-- Sélection de la ville -->
                                        <div class="mb-3">
                                            <label for="ville" class="form-label fw-bold">
                                                <span class="text-danger">*</span> Ville affectée
                                            </label>
                                            <select class="form-select form-select-lg" id="ville" name="ville" required>
                                                <option value="" selected disabled>-- Sélectionnez une ville --</option>
                                                <?php if (!empty($villes) && is_array($villes)): ?>
                                                    <?php foreach ($villes as $v): ?>
                                                        <?php
                                                            // compatibilité champs: id/nom ou ID/NOM
                                                            $vid = $v['id'] ?? $v['ID'] ?? null;
                                                            $vnom = $v['nom'] ?? $v['NOM'] ?? $v['name'] ?? null;
                                                        ?>
                                                        <?php if ($vid !== null && $vnom !== null): ?>
                                                            <option value="<?= htmlspecialchars($vid) ?>"><?= htmlspecialchars($vnom) ?></option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <!-- Fallback statique si la table ville est vide ou indisponible -->
                                                    <option value="1">Antananarivo</option>
                                                    <option value="2">Toamasina</option>
                                                    <option value="3">Mahajanga</option>
                                                <?php endif; ?>
                                            </select>
                                            <div class="form-text">Choisissez la ville qui a besoin d'assistance</div>
                                        </div>

                                        <!-- Sélection du produit -->
                                        <div class="mb-3">
                                            <label for="produit" class="form-label fw-bold">
                                                <span class="text-danger">*</span> Type de produit nécessaire
                                            </label>
                                            <select class="form-select form-select-lg" id="produit" name="produit" required>
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
                                            <div class="form-text">Sélectionnez le type de produit dont la ville a besoin</div>
                                        </div>

                                        <!-- Quantité -->
                                        <div class="mb-3">
                                            <label for="quantite" class="form-label fw-bold">
                                                <span class="text-danger">*</span> Quantité nécessaire
                                            </label>
                                            <input type="number" class="form-control form-control-lg" id="quantite" 
                                                   name="quantite" min="1" step="1" required placeholder="Exemple: 100">
                                            <div class="form-text">Indiquez la quantité nécessaire (nombre entier positif)</div>
                                        </div>

                                        <!-- Date -->
                                        <div class="mb-3">
                                            <label for="date" class="form-label fw-bold">
                                                <span class="text-danger">*</span> Date du besoin
                                            </label>
                                            <input type="date" class="form-control form-control-lg" id="date" 
                                                   name="date" required value="<?php echo date('Y-m-d'); ?>">
                                            <div class="form-text">Date d'enregistrement du besoin (par défaut: aujourd'hui)</div>
                                        </div>

                                        <!-- Boutons -->
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                                <strong>Enregistrer le besoin</strong>
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
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . $base . '/includes/footer.php'; ?>
    
    <script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script<?php if (!empty($nonce)): ?> nonce="<?= htmlspecialchars($nonce) ?>"<?php endif; ?>>

</body>

</html>
