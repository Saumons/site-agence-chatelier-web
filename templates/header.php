<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $pageTitle ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400&family=Montserrat:wght@300;800&family=Sora:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if($pageTitle === "Formulaire de devis"): ?>
        <script defer type="module" src="assets/js/devis.js"></script>
    <?php endif ?>
</head>

<body>
<header class="header">
    <a class="header__brand" href="accueil" aria-label="Chat'elier Web, accueil">
        <svg class="header__logo" viewBox="0 0 64.532997 90.939003" aria-hidden="true">
            <use href="assets/images/ChatelierLogoSeul.svg#layer1"></use>
        </svg>
        <div class="header__titre-entreprise">
            <p class="header__nom">CHAT'ELIER</p>
            <p class="header__sous-titre">WEB</p>
        </div>
    </a>

    <div class="header__element-droite">
        <nav aria-label="Navigation principale" class="header__menu">
            <input type="checkbox" id="toggle" aria-label="Ouvrir le menu">
            <label class="header__burger" for="toggle" aria-hidden="true">
                <span></span><span></span><span></span>
            </label>
            <div class="header__pages">
                <a href="nosRealisations">Nos réalisations</a>
                <a href="nosServices">Nos services</a>
                <a href="nosEngagements">Nos engagements</a>
                <a href="contact" class="header__contact-menu">Contacter l'équipe</a>
            </div>
        </nav>


        <button class="header__mode-nuit" id="modeNuit" type="button" aria-label="Activer le mode nuit" aria-pressed="false">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z" />
            </svg>
        </button>

        <a href="contact" class="header__contact">Ecrire a l'équipe</a>
    </div>
</header>