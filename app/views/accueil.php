<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue</title>
    <?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
    <style>
        :root{ --accent:#2b8cff; }
        html,body{ height:100%; margin:0; font-family:Segoe UI, Roboto, "Helvetica Neue", Arial, sans-serif; }
        .hero{
            height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            text-align:center;
            color:#fff;
            background-image: url('<?= htmlspecialchars($base) ?>/assets/images/accueil.jpg');
            background-size:cover;
            background-position:center center;
            position:relative;
        }
        .overlay{ position:absolute; inset:0; background:rgba(0,0,0,0.45); }
        .container{ position:relative; z-index:2; padding:1.5rem; max-width:980px; }
        h1{ font-size:3rem; margin:0 0 0.5rem; text-transform:uppercase; letter-spacing:1px; }
        p.lead{ font-size:1.125rem; margin:0 0 1.25rem; opacity:0.95 }
        .btn-start{
            display:inline-block; padding:0.75rem 1.25rem; font-size:1.05rem; color:#fff; background:var(--accent); border-radius:8px; text-decoration:none; box-shadow:0 6px 18px rgba(43,140,255,0.28);
        }
        .btn-start:active{ transform:translateY(1px); }

        /* responsive */
        @media (max-width:600px){
            h1{ font-size:2rem }
            .hero{ padding:2rem 0 }
        }
    </style>
</head>
<body>
    <section class="hero" role="img" aria-label="Image d'accueil">
        <div class="overlay" aria-hidden="true"></div>
        <div class="container">
            <h1>Bienvenue dans le site de collecte de BNGRC</h1>            
            <h3>Nous avons besoin de votre aide</h3>
            <p>Votre contribution est essentielle pour répondre aux besoins de la communauté.</p>
            <p>
                <a class="btn-start" href="<?= htmlspecialchars($base) ?>/tableauBord">Commencer</a>
            </p>
        </div>
    </section>
</body>
</html>