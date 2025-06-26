<?php
namespace Controllers;

use Core\Response;
use Src\Models\Image;
use Src\Models\ProduitImage;

class UploadImageController {

    public function uploadMultiple(): void {
        $productId = $_POST['id_produit'] ?? null;
        $files = $_FILES['images'] ?? null;

        if (!$productId || !$files || empty($files['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Fichiers ou produit manquant']);
            return;
        }

        $imageModel = new Image();
        $produitImageModel = new ProduitImage();

        // Normaliser en tableau même si un seul fichier est envoyé
        $fileNames = is_array($files['name']) ? $files['name'] : [$files['name']];
        $fileTmpNames = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];

        $uploaded = [];

        for ($i = 0; $i < count($fileNames); $i++) {
            $name = $fileNames[$i];
            $tmp = $fileTmpNames[$i];

            $path = 'uploads/produits/' . uniqid() . '_' . basename($name);
            if (!move_uploaded_file($tmp, $path)) continue;

            $idImage = $imageModel->create($path);
            if ($idImage) {
                $produitImageModel->create([
                    'id_produit' => (int)$productId,
                    'id_image'   => $idImage
                ]);
                $uploaded[] = $path;
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'message' => 'Images traitées',
            'uploaded' => $uploaded
        ]);
    }
}
