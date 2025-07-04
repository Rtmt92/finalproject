<?php
// src/Controllers/ProduitController.php
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

    public function __construct(
        ?Produit $produitModel = null,
        ?ProduitImage $produitImageModel = null,
        ?Image $imageModel = null
    ) {
        $this->produitModel   = $produitModel   ?? new Produit();
        $this->prodImageModel = $produitImageModel ?? new ProduitImage();
        $this->imageModel     = $imageModel     ?? new Image();
        $this->db             = Database::getConnection();
    }

    /**
     * GET /produit
     */
    public function index(): void {
        header('Content-Type: application/json; charset=utf-8');

        $categorie = $_GET['categorie'] ?? null;
        $etat      = $_GET['etat']      ?? null;
        $search    = $_GET['q']         ?? null;

        $produits  = $this->produitModel->filter($categorie, $etat, $search);
        $results   = [];

        foreach ($produits as $p) {
            // récupérer première image
            $pivots   = $this->prodImageModel->getImagesByProduitId($p['id_produit']);
            $imageUrl = null;
            if (!empty($pivots[0]['id_image'])) {
                $img = $this->imageModel->getById((int)$pivots[0]['id_image']);
                if ($img && !empty($img['lien'])) {
                    $imageUrl = $img['lien'];
                }
            }
            $results[] = [
                'id'          => $p['id_produit'],
                'titre'       => $p['nom_produit'],
                'description' => $p['description'],
                'prix'        => $p['prix'],
                'etat'        => $p['etat']     ?? '',
                'quantite'    => $p['quantite'] ?? 0,
                'image'       => $imageUrl,
            ];
        }

        echo json_encode($results);
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

        // ajouter toutes les images
        $pivots = $this->prodImageModel->getImagesByProduitId($id);
        $images = [];
        foreach ($pivots as $pv) {
            $img = $this->imageModel->getById((int)$pv['id_image']);
            if ($img) $images[] = $img;
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
            !isset($data['prix']) || !is_numeric($data['prix']) ||
            empty($data['description']) ||
            !isset($data['id_categorie']) ||
            !isset($data['quantite']) || !is_numeric($data['quantite']) ||
            empty($data['etat'])
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs manquants ou invalides']);
            return;
        }

        $newId = $this->produitModel->create([
            'nom_produit'  => $data['nom_produit'],
            'prix'         => (float)$data['prix'],
            'description'  => $data['description'],
            'id_categorie' => (int)$data['id_categorie'],
            'quantite'     => (int)$data['quantite'],
            'etat'         => $data['etat'],
        ]);

        if ($newId) {
            http_response_code(201);
            echo json_encode(['message' => 'Produit créé', 'id_produit' => $newId]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de créer']);
        }
    }

    /**
     * DELETE /produit/{id}/image/{image}
     */
    public function deleteImage(int $idProduit, int $idImage): void {
        header('Content-Type: application/json; charset=utf-8');

        $ok = $this->prodImageModel->detachImageFromProduct($idProduit, $idImage);
        if (!$ok) {
            http_response_code(400);
            echo json_encode(['error' => 'Impossible de détacher l’image']);
            return;
        }

        // si l’image n’est plus utilisée ailleurs, on la supprime
        if (!$this->imageModel->isUsedElsewhere($idImage)) {
            $this->imageModel->delete($idImage);
        }

        echo json_encode(['message' => 'Image supprimée']);
    }

    /**
     * POST /produit/{id}/image
     */
    public function uploadImage(int $id_produit): void {
        header('Content-Type: application/json; charset=utf-8');

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

        // préparation du dossier
        $uploadDir = __DIR__ . '/../public/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('', true) . '.' . $ext;
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            http_response_code(500);
            echo json_encode(['error' => 'Échec enregistrement image']);
            exit;
        }

        // détecter HTTP ou HTTPS
        $scheme     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host       = $_SERVER['HTTP_HOST'];
        $publicLink = "{$scheme}://{$host}/uploads/{$fileName}";

        // enregistrer en base + association produit
        $idImage = $this->imageModel->create(['lien' => $publicLink]);
        $this->prodImageModel->create([
            'id_produit' => $id_produit,
            'id_image'   => $idImage
        ]);

        // répondre
        echo json_encode([
            'success'  => true,
            'id_image' => $idImage,
            'url'      => $publicLink,
        ]);
    }

    /**
     * PUT/PATCH /produit/{id}
     */
    public function update(int $id): void {
        header('Content-Type: application/json; charset=utf-8');

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

        // si quantité à 0 → suppression
        if (isset($data['quantite']) && (int)$data['quantite'] === 0) {
            $deleted = $this->produitModel->destroy($id);
            if ($deleted) {
                echo json_encode(['message' => 'Produit supprimé (quantité 0)']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur suppression']);
            }
            return;
        }

        $idCat = (isset($data['id_categorie']) && $data['id_categorie'] !== '')
            ? (int)$data['id_categorie']
            : null;

        $ok = $this->produitModel->update($id, [
            'nom_produit'  => $data['nom_produit']  ?? null,
            'prix'         => isset($data['prix']) ? (float)$data['prix'] : null,
            'description'  => $data['description']  ?? null,
            'id_categorie' => $idCat,
            'quantite'     => isset($data['quantite']) ? (int)$data['quantite'] : null,
            'etat'         => $data['etat'] ?? null,
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
    public function destroy(int $id): void {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $this->db->prepare("DELETE FROM panier_produit WHERE id_produit = :id")
                     ->execute(['id' => $id]);

            $this->db->prepare("DELETE FROM produit_image WHERE id_produit = :id")
                     ->execute(['id' => $id]);

            $this->db->prepare("DELETE FROM produit WHERE id_produit = :id")
                     ->execute(['id' => $id]);

            echo json_encode(['message' => 'Produit supprimé avec succès']);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de supprimer']);
        }
    }

    /**
     * GET /produit/random
     */
    public function random(): void {
        header('Content-Type: application/json; charset=utf-8');

        $prod = $this->produitModel->findRandom();
        if (!$prod) {
            http_response_code(404);
            echo json_encode(['error' => 'Aucun produit trouvé']);
            return;
        }

        $pivots = $this->prodImageModel->getImagesByProduitId((int)$prod['id_produit']);
        $url    = null;
        if (!empty($pivots[0]['id_image'])) {
            $img = $this->imageModel->getById((int)$pivots[0]['id_image']);
            if ($img && !empty($img['lien'])) {
                $url = $img['lien'];
            }
        }
        $prod['image_url'] = $url;

        echo json_encode($prod);
    }
}
