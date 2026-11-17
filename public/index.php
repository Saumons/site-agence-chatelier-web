<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Env;

Env::load(__DIR__ . '/../.env');

$pageTitle = 'Accueil — SA Agence Web';
$active    = 'home';

require __DIR__ . '/../templates/header.php';
require __DIR__ . '/../templates/home.php';
require __DIR__ . '/../templates/footer.php';
