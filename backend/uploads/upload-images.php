<?php
require_once '../vendor/autoload.php';

use Core\Database;

header('Content-Type: application/json');

try {
    if (!isset($_FILES['images'])) {
        throw new Exception("Aucune image reçue.");
    }

    $db = Database::getConnection();
    $uploaded = [];

    foreach ($_FILES['images']['tmp_name'] as $index => $tmpPath) {
        $originalName = $_FILES['images']['name'][$index];
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = 'image_' . uniqid() . '.' . $extension;
        $targetDir = __DIR__ . '/../uploads/produits/';
        $targetPath = $targetDir . $filename;

        // Créer le dossier s'il n'existe pas
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (!move_uploaded_file($tmpPath, $targetPath)) {
            throw new Exception("Échec du déplacement du fichier : $originalName");
        }

        // Insérer en BDD
        $stmt = $db->prepare("INSERT INTO image (link) VALUES (:link)");
        $stmt->execute(['link' => '/uploads/produits/' . $filename]);
        $id = $db->lastInsertId();

        $uploaded[] = [
            'id_image' => $id,
            'link' => '/uploads/produits/' . $filename
        ];
    }

    echo json_encode($uploaded);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
