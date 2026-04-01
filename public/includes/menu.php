<?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
<h5 class="mb-3">Menu</h5>
<ul class="nav flex-column">
    <li class="nav-item mb-2">
        <a class="nav-link text-white active" href="<?= htmlspecialchars($base) ?>/tableauBord">
            <img src="<?= htmlspecialchars($base) ?>/assets/icons/home.png" alt="tableau de bord" class="icon-menu">
            Tableau de Bord
        </a>
    </li>
    <li class="nav-item mb-2">
        <a class="nav-link text-white" href="<?= htmlspecialchars($base) ?>/besoins/formulaire">
            <img src="<?= htmlspecialchars($base) ?>/assets/icons/boxes.png" alt="besoins" class="icon-menu">
            Saisir Besoins
        </a>
    </li>
    <li class="nav-item mb-2">
        <a class="nav-link text-white" href="<?= htmlspecialchars($base) ?>/besoins/liste">
            <img src="<?= htmlspecialchars($base) ?>/assets/icons/boxes.png" alt="home" class="icon-menu">
            Liste Besoins
        </a>
    </li>
    <li class="nav-item mb-2">
        <a class="nav-link text-white" href="<?= htmlspecialchars($base) ?>/dons/formulaire">
            <img src="<?= htmlspecialchars($base) ?>/assets/icons/object1.png" alt="dons" class="icon-menu">
            Saisir Dons
        </a>
    </li>
    <li class="nav-item mb-2">
        <a class="nav-link text-white" href="<?= htmlspecialchars($base) ?>/dons/liste">
            <img src="<?= htmlspecialchars($base) ?>/assets/icons/object1.png" alt="dons" class="icon-menu">
            Liste Dons
        </a>
    </li>
    <li class="nav-item mb-2">
        <a class="nav-link text-white" href="<?= htmlspecialchars($base) ?>/villes/liste">
            <img src="<?= htmlspecialchars($base) ?>/assets/icons/home.png" alt="villes" class="icon-menu">
            Liste Villes
        </a>
    </li>
    <li class="nav-item mb-2">
        <a class="nav-link text-white" href="<?= htmlspecialchars($base) ?>/dispatch">
            <img src="<?= htmlspecialchars($base) ?>/assets/icons/proposition.png" alt="dispatch" class="icon-menu">
            Dispatch des Dons
        </a>
    </li>
    <li class="nav-item mb-2">
        <a class="nav-link text-white" href="<?= htmlspecialchars($base) ?>/besoins-restants">
            <img src="<?= htmlspecialchars($base) ?>/assets/icons/boxes.png" alt="besoins-restants" class="icon-menu">
            Besoins restants (achats)
        </a>
    </li>

    <li class="nav-item mb-2">
        <a class="nav-link text-white" href="<?= htmlspecialchars($base) ?>/achats">
            <img src="<?= htmlspecialchars($base) ?>/assets/icons/receipt.png" alt="achats" class="icon-menu">
            Achats
        </a>
    </li>

    <li class="nav-item mb-2">
        <a class="nav-link text-white" href="<?= htmlspecialchars($base) ?>/achats/proposer">
            <img src="<?= htmlspecialchars($base) ?>/assets/icons/proposition.png" alt="achats-manuel" class="icon-menu">
            Effectuer un Achat
        </a>
    </li>

    <li class="nav-item mb-2">
        <a class="nav-link text-white" href="<?= htmlspecialchars($base) ?>/recap">
            <img src="<?= htmlspecialchars($base) ?>/assets/icons/home.png" alt="recap" class="icon-menu">
            📊 Récapitulatif
        </a>
    </li>

</ul>