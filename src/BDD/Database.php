<?php

namespace App\BDD;

use App\Env;
use PDO;
use PDOException;

/**
 * Connexion PDO à la base de données.
 *
 * Non utilisée dans ce squelette de démo (2 pages placeholder), mais prête
 * à l'emploi pour la suite du projet (ex: enregistrer les messages du
 * formulaire de contact en base plutôt que de les envoyer par email).
 */
final class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = Env::get('DB_HOST', '127.0.0.1');
            $port = Env::get('DB_PORT', '3306');
            $name = Env::get('DB_NAME', '');
            $user = Env::get('DB_USER', 'root');
            $pass = Env::get('DB_PASS', '');

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                throw new PDOException('Connexion BDD impossible : ' . $e->getMessage(), (int) $e->getCode());
            }
        }

        return self::$instance;
    }
}
