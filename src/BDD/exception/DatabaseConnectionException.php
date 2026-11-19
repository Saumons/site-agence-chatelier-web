<?php

namespace App\BDD\exception;

use Throwable;

/**
 * Représente une erreur survenue lors de la connexion
 * à la base de données.
 *
 * Cette exception est généralement levée lorsqu'il est
 * impossible d'établir une connexion PDO
 * – hôte inaccessible
 * – base inexistante
 * – identifiants invalides
 * – serveur de base de données indisponible.
 */
final class DatabaseConnectionException extends DatabaseException
{
    public function __construct(
        string $message = "Impossible de se connecter à la base de données.",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
