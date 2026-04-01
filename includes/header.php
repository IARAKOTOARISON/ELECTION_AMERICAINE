<?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-custom-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold fs-4" href="<?= htmlspecialchars($base) ?>/">
            <img src="<?= htmlspecialchars($base) ?>/assets/icons/LOGO.png" alt="Takalo Logo" class="icon-logo">
             COLLECTE BNGRC
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars($base) ?>/logout">Déconnexion</a>
                </li>
            </ul>
        </div> -->
    </div>
</nav>