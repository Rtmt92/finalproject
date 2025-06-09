<?php
namespace Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $host = '127.0.0.1';
                $db   = 'dejavu';
                $user = 'root';
                $pass = 'admin';
                $dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                // En cas d’erreur, on meurt avec le message
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'DB Connection failed: '.$e->getMessage()]);
                exit;
            }
        }

        return self::$instance;
    }
}
