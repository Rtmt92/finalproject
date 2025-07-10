<?php
namespace Controllers;

use Src\Services\ProduitService;

class ProduitController {
    private ProduitService $service; // Service pour gérer les produits

    public function __construct() {
        // Initialise le service produit
        $this->service = new ProduitService();
    }

    // GET /produits : liste tous les produits avec filtres et images
    public function index(): void {
        header('Content-Type: application/json; charset=utf-8');
        $categorie = $_GET['categorie'] ?? null;
        $etat      = $_GET['etat']      ?? null;
        $search    = $_GET['q']         ?? null;
        $results   = $this->service->getAllWithImages($categorie, $etat, $search);
        echo json_encode($results);
    }

    // GET /produits/{id} : affiche un produit et ses images
    public function show(int $id): void {
        $item = $this->service->getById($id);
        if (!$item) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Produit non trouvé']);
            return;
        }
        $item['images'] = $this->service->getImagesByProduitId($id);
        header('Content-Type: application/json');
        echo json_encode($item);
    }

    // POST /produits : crée un nouveau produit
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (
            empty($data['nom_produit']) ||
            !isset($data['prix']) || !is_numeric($data['prix']) ||
            empty($data['description']) ||
            !isset($data['id_categorie']) ||
            !isset($data['quantite'])   || !is_numeric($data['quantite']) ||
            empty($data['etat'])
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs manquants ou invalides']);
            return;
        }
        $newId = $this->service->create([
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

    // DELETE /produits/{idProduit}/images/{idImage} : détache une image d’un produit
    public function deleteImage(int $idProduit, int $idImage): void {
        header('Content-Type: application/json; charset=utf-8');
        $ok = $this->service->detachImageFromProduct($idProduit, $idImage);
        if (!$ok) {
            http_response_code(400);
            echo json_encode(['error' => 'Impossible de détacher l’image']);
            return;
        }
        echo json_encode(['message' => 'Image supprimée']);
    }

    // POST /produits/{idProduit}/upload : uploade et associe une image au produit
    public function uploadImage(int $id_produit): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_FILES['image'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Aucun fichier envoyé']);
            exit;
        }
        $file      = $_FILES['image'];
        $uploadDir = __DIR__ . '/../public/uploads/';
        $scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host      = $_SERVER['HTTP_HOST'];
        $publicLink = "{$scheme}://{$host}/uploads";
        $result = $this->service->uploadImage($id_produit, $file, $uploadDir, $publicLink);
        if ($result === null) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors du téléchargement ou enregistrement']);
            return;
        }
        echo json_encode([
            'success'  => true,
            'id_image' => $result['id_image'],
            'url'      => $result['url'],
        ]);
    }

    // PUT|PATCH /produits/{id} : met à jour un produit (ou supprime si quantité à 0)
    public function update(int $id): void {
        header('Content-Type: application/json; charset=utf-8');
        $existing = $this->service->getById($id);
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
        if (isset($data['quantite']) && (int)$data['quantite'] === 0) {
            $deleted = $this->service->destroy($id);
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
        $ok = $this->service->update($id, [
            'nom_produit'  => $data['nom_produit']  ?? null,
            'prix'         => isset($data['prix'])   ? (float)$data['prix'] : null,
            'description'  => $data['description']   ?? null,
            'id_categorie' => $idCat,
            'quantite'     => isset($data['quantite']) ? (int)$data['quantite'] : null,
            'etat'         => $data['etat']         ?? null,
        ]);
        if ($ok) {
            echo json_encode(['message' => 'Produit mis à jour']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de mettre à jour']);
        }
    }

    // DELETE /produits/{id} : supprime définitivement un produit
    public function destroy(int $id): void {
        header('Content-Type: application/json; charset=utf-8');
        $deleted = $this->service->destroy($id);
        if ($deleted) {
            echo json_encode(['message' => 'Produit supprimé avec succès']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de supprimer']);
        }
    }

    // GET /produits/random : renvoie un produit aléatoire avec sa première image
    public function random(): void {
        header('Content-Type: application/json; charset=utf-8');
        $prod = $this->service->findRandom();
        if (!$prod) {
            http_response_code(404);
            echo json_encode(['error' => 'Aucun produit trouvé']);
            return;
        }
        $prod['image_url'] = $this->service->getFirstImageUrl((int)$prod['id_produit']) ?? null;
        echo json_encode($prod);
    }
}
