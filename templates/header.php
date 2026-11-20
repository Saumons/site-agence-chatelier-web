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
</head>

<body>
    <header class="header">

        <svg class="header__logo" viewBox="0 0 64.532997 90.939003">
            <use href="assets/images/ChatelierLogoSeul.svg#layer1"></use>
        </svg>

        <div class="header__titre-entreprise">
            <p class="chat-elier">CHAT'ELIER</p>
            <p class="web">WEB</p>
        </div>

        
            <svg class="header__modeNuit lune" role="button" tabindex="0" aria-label="Mode nuit" viewBox="0 0 64.532997 90.939003">
                <use href="assets/images/lune.svg#layer1"></use>
            </svg>
        

        <nav>
            <label class="header__burger" for="toggle">☰</label>
            <input type="checkbox" id="toggle">
            <div class="main_pages">
                <a href="#">Accueil</a>
                <a href="#">Actualité</a>
                <a href="#">Contact</a>
            </div>
        </nav>

    </header>