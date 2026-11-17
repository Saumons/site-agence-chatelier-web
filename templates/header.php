<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'SA — Agence Web', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="SA, agence web : création de sites et applications sur-mesure.">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container site-header__inner">
        <a href="/" class="logo">SA<span>.</span></a>
        <nav class="main-nav">
            <ul>
                <li><a href="/index.php" <?= ($active ?? '') === 'home' ? 'class="is-active"' : '' ?>>Accueil</a></li>
                <li><a href="/contact.php" <?= ($active ?? '') === 'contact' ? 'class="is-active"' : '' ?>>Contact</a></li>
            </ul>
        </nav>
        <button class="burger" type="button" aria-label="Ouvrir le menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>
<main>
