<?php
// Opens one shared PDO connection to MySQL. The credentials come from config/config.php
// (git-ignored); config.example.php shows which constants it must define.

require_once __DIR__ . '/../config/config.php';

class Database
{
    private static ?PDO $connection = null;

    public static function connect(): PDO
    {
        // Reuse the same connection for every DAO during one request.
        if (self::$connection === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

            self::$connection = new PDO($dsn, DB_USER, DB_PASSWORD, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // SQL errors throw a PDOException
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows come back as ['column' => value]
                PDO::ATTR_EMULATE_PREPARES   => false,                  // real prepared statements on the server
            ]);
        }

        return self::$connection;
    }
}
