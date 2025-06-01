<?php
require_once __DIR__ . '/vendor/autoload.php';

use Core\Database;

try {
    $db = Database::getConnection();
    echo json_encode(['status' => 'success', 'message' => 'Connexion OK']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
