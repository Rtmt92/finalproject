<?php
namespace Controllers;

use Src\Models\Produit;
use Src\Models\ProduitImage;
use Src\Models\Image;
use Core\Database;

class ProduitController {
    private Produit $produitModel;
    private ProduitImage $prodImageModel;
    private Image $imageModel;
    private \PDO $db;

    public function __construct() {
        $this->produitModel   = new Produit();
        $this->prodImageModel = new ProduitImage();
        $this->imageModel     = new Image();
        $this->db             = Database::getConnection();
    }

    /**
     * GET /produit
     */
    public function index(): void {
        header('Content-Type: application/json; charset=utf-8');

        $categorieId = $_GET['categorie'] ?? null;

        if ($categorieId) {
            $produits = $this->produitModel->getByCategorie((int)$categorieId);
        } else {
            $produits = $this->produitModel->getAll();
        }

        $resultats = [];

        foreach ($produits as $prod) {
            $images = $this->prodImageModel->getImagesByProduitId($prod['id_produit']);
            $imageUrl = null;

            if (!empty($images[0]['id_image'])) {
                $img = $this->imageModel->getById((int)$images[0]['id_image']);
                if ($img && !empty($img['lien'])) {
                    $imageUrl = $img['lien'];
                }
            }

            $resultats[] = [
                'id'          => $prod['id_produit'],
                'titre'       => $prod['nom_produit'],
                'description' => $prod['description'],
                'prix'        => $prod['prix'],
                'image'       => $imageUrl,
            ];
        }

        echo json_encode($resultats);
    }

    /**
     * GET /produit/{id}
     */
    public function show(int $id): void {
        $item = $this->produitModel->getById($id);
        if (!$item) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Produit non trouvé']);
            return;
        }

        $pivots = $this->prodImageModel->getImagesByProduitId($id);
        $images = [];
        foreach ($pivots as $pivot) {
            $img = $this->imageModel->getById((int)$pivot['id_image']);
            if ($img) {
                $images[] = $img;
            }
        }
        $item['images'] = $images;

        header('Content-Type: application/json');
        echo json_encode($item);
    }

    /**
     * POST /produit
     */
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (
            empty($data['nom_produit']) ||
            empty($data['prix']) ||
            empty($data['description']) ||
            empty($data['id_categorie'])
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }

        $newId = $this->produitModel->create([
            'nom_produit'  => $data['nom_produit'],
            'prix'         => $data['prix'],
            'description'  => $data['description'],
            'id_categorie' => (int)$data['id_categorie'],
        ]);

        if ($newId) {
            http_response_code(201);
            echo json_encode(['message' => 'Produit créé', 'id_produit' => $newId]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de créer']);
        }
    }

    public function deleteImage(int $idProduit, int $idImage) {
        $produitImageModel = new ProduitImage();
        $imageModel = new Image();

        // Supprimer la liaison
        $success = $produitImageModel->detachImageFromProduct($idProduit, $idImage);

        if (!$success) {
            http_response_code(400);
            echo json_encode(['error' => "Impossible de détacher l'image du produit"]);
            return;
        }

        // Supprimer l'image si plus utilisée
        if (!$imageModel->isUsedElsewhere($idImage)) {
            $imageModel->delete($idImage);
        }

        echo json_encode(['message' => "Image supprimée avec succès"]);
    }

public function uploadImage(int $id_produit){
        if (!isset($_FILES['image'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Aucun fichier envoyé']);
            exit;
        }

        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'Erreur lors du téléchargement']);
            exit;
        }

        // Créer le dossier si besoin
        $uploadDir = __DIR__ . '/../public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $extension;
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            http_response_code(500);
            echo json_encode(['error' => 'Échec de l’enregistrement de l’image']);
            exit;
        }

        // Générer l'URL accessible depuis le frontend
        $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        $publicLink = $baseUrl . '/uploads/' . $fileName;

        // Enregistrer dans la base de données
        $imageModel = new \Src\Models\Image();
        $imageModel->saveForProduct($id_produit, $publicLink);

        echo json_encode([
            'success' => true,
            'url' => $publicLink
        ]);
    }





    /**
     * PUT/PATCH /produit/{id}
     */
    public function update(int $id): void {
        $existing = $this->produitModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Produit non trouvé']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données invalides']);
            return;
        }
        $ok = $this->produitModel->update($id, [
            'nom_produit'  => $data['nom_produit']  ?? null,
            'prix'         => $data['prix']         ?? null,
            'description'  => $data['description']  ?? null,
            'id_categorie' => isset($data['id_categorie']) ? (int)$data['id_categorie'] : null,
        ]);
        if ($ok) {
            echo json_encode(['message' => 'Produit mis à jour']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de mettre à jour']);
        }
    }

    /**
     * DELETE /produit/{id}
     */
public function destroy(int $id) {
    try {
        $this->db = \Core\Database::getConnection();

        // 1. Supprimer les références dans les tables liées
        $this->db->prepare("DELETE FROM panier_produit WHERE id_produit = :id")
                 ->execute(['id' => $id]);

        $this->db->prepare("DELETE FROM produit_image WHERE id_produit = :id")
                 ->execute(['id' => $id]);

        // 2. Supprimer le produit
        $stmt = $this->db->prepare("DELETE FROM produit WHERE id_produit = :id");
        $stmt->execute(['id' => $id]);

        echo json_encode(["message" => "Produit supprimé avec succès"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Impossible de supprimer"]);
    }
}



    /**
     * GET /produit/random
     */
    public function random(): void {
        header('Content-Type: application/json');
        $prod = $this->produitModel->findRandom();
        if (!$prod) {
            http_response_code(404);
            echo json_encode(['error' => 'Aucun produit trouvé']);
            return;
        }
        $pivot = $this->prodImageModel->getImagesByProduitId((int)$prod['id_produit']);
        $url   = null;
        if (!empty($pivot[0]['id_image'])) {
            $img = $this->imageModel->getById((int)$pivot[0]['id_image']);
            if ($img && !empty($img['lien'])) {
                $url = $img['lien'];
            }
        }
        $prod['image_url'] = $url;
        echo json_encode($prod);
    }
}
