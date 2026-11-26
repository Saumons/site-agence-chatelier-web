<?php
define('BASE_URL', 'site-agence/');   // TODO : en production : ''
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
