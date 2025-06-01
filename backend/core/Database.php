<?php
namespace Core;

use PDO;
use PDOException;
use Config\DatabaseConfig;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DatabaseConfig::$host . ";dbname=" . DatabaseConfig::$dbname . ";charset=" . DatabaseConfig::$charset;
            try {
                self::$instance = new PDO($dsn, DatabaseConfig::$username, DatabaseConfig::$password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                die("Erreur de connexion : " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
