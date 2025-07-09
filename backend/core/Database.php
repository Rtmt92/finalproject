<?php
namespace Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {

            if (!isset($_ENV['DB_HOST'])) {
                $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
                $dotenv->load();
            }

            $host     = $_ENV['DB_HOST']     ?? '127.0.0.1';
            $dbname   = $_ENV['DB_DATABASE'] ?? 'dejavu';
            $username = $_ENV['DB_USERNAME'] ?? 'dejavu';
            $password = $_ENV['DB_PASSWORD'] ?? 'admin';
            $charset  = $_ENV['DB_CHARSET']  ?? 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

            try {
                self::$instance = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
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
