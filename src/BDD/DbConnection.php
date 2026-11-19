<?php

namespace App\BDD;

use App\BDD\exception\DatabaseConnectionException;
use PDO;
use PDOException;

/**
 * Class qui gère la connection à la DB
 */
final class DbConnection
{
    /**
     * Ouvre la connexion à la DB
     * @return PDO objet de connexion
     * @throws DatabaseConnectionException Si connexion impossible
     */
    public static function create(): PDO
    {

        $requiredEnv = [
            'DB_HOST',
            'DB_NAME',
            'DB_USER',
            'DB_PASSWORD'
        ];
        // Test si les variables environments sont présentes
        foreach ($requiredEnv as $envKey) {

            if (
                !isset($_ENV[$envKey]) ||
                trim($_ENV[$envKey]) === ''
            ) {
                throw new DatabaseConnectionException(
                    sprintf(
                        'La variable d\'environnement "%s" est manquante ou vide.',
                        $envKey
                    )
                );
            }
        }

        try {
            return new PDO(
                sprintf(
                    'mysql:host=%s;dbname=%s;charset=utf8mb4',
                    $_ENV['DB_HOST'],
                    $_ENV['DB_NAME']
                ),
                $_ENV['DB_USER'],
                $_ENV['DB_PASSWORD'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]
            );
        } catch (PDOException $e) {

            error_log(
                sprintf(
                    '[%s] Erreur de connexion BDD : %s',
                    date('Y-m-d H:i:s'),
                    $e->getMessage()
                )
            );

            throw new DatabaseConnectionException(
                'Impossible de se connecter à la base de données.',
                0,
                $e
            );
        }
    }
}
