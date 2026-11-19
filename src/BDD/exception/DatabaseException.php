<?php

namespace App\BDD\exception;

use RuntimeException;
use Throwable;

/**
 * Représente une erreur survenue dans la couche
 * d'accès aux données de l'application.
 *
 * Cette exception sert de classe parente aux exceptions
 * spécialisées liées à la base de données.
 */
class DatabaseException extends RuntimeException
{
    public function __construct(
        string $message = "Une erreur de base de données est survenue.",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
