<?php

namespace App\BDD\exception;

use Throwable;

/**
 * Représente une erreur survenue lors de la lecture
 * de données depuis la base de données.
 *
 * Cette exception est généralement levée lors
 * – d'un SELECT
 * – d'une erreur de préparation de requête
 * – d'une erreur d'exécution
 * – d'une erreur de récupération des résultats.
 */
final class DatabaseReadException extends DatabaseException
{
    public function __construct(
        string $message = "Une erreur est survenue lors de la lecture des données.",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
