<?php
$dotenv = Dotenv\Dotenv::createImmutable(
    dirname(__DIR__, 2)
);

$dotenv->load();