<?php

namespace App\BDD\exception;

use Throwable;

/**
 * Représente une erreur survenue lors de l'écriture
 * de données dans la base de données.
 *
 * Cette exception est généralement levée lors
 * – d'un INSERT
 * – d'une UPDATE
 * – d'un DELETE
 * – d'une violation de contrainte
 * – d'une erreur d'intégrité référentielle.
 */
final class DatabaseWriteException extends DatabaseException
{
    public function __construct(
        string     $message = "Une erreur est survenue lors de l'écriture des données.",
        int        $code = 0,
        ?Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
