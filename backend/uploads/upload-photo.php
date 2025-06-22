<?php
$uploadDir = __DIR__ . '/uploads/profils/';
$baseUrl = 'http://localhost:3000/uploads/profils/';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

if (!isset($_FILES['photo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Fichier manquant']);
    exit;
}

$file = $_FILES['photo'];
$allowedTypes = ['image/jpeg', 'image/png'];
$maxSize = 5 * 1024 * 1024; // 5 MB

if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Type de fichier invalide']);
    exit;
}

if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'Fichier trop volumineux']);
    exit;
}

// Créer le dossier si besoin
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('profil_', true) . '.' . $ext;

if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
    http_response_code(500);
    echo json_encode(['error' => 'Échec de l’upload']);
    exit;
}

echo json_encode([
    'url' => $baseUrl . $filename,
    'filename' => $filename
]);
