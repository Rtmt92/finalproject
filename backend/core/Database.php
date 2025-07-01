<?php
namespace Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    // Configuration centralisée
    private static string $host     = '127.0.0.1';
    private static string $dbname   = 'dejavu';
    private static string $username = 'root';
    private static string $password = 'admin';
    private static string $charset  = 'utf8mb4';

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=" . self::$charset;

            try {
                self::$instance = new PDO($dsn, self::$username, self::$password, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'DB Connection failed: ' . $e->getMessage()]);
                exit;
            }
        }

        return self::$instance;
    }
}
