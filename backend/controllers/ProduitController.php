<?php
namespace Controllers;

use Src\Models\Produit;
use Src\Models\ProduitImage;
use Src\Models\Image;

class ProduitController {
    private Produit $produitModel;
    private ProduitImage $prodImageModel;
    private Image $imageModel;

    public function __construct() {
        $this->produitModel   = new Produit();
        $this->prodImageModel = new ProduitImage();
        $this->imageModel      = new Image();
    }

    /**
     * GET /produit
     */
    public function index(): void {
        header('Content-Type: application/json');
        echo json_encode($this->produitModel->getAll());
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

        // Récupère toutes les images associées
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
    public function destroy(int $id): void {
        $existing = $this->produitModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Produit non trouvé']);
            return;
        }
        if ($this->produitModel->delete($id)) {
            echo json_encode(['message' => 'Produit supprimé']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de supprimer']);
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
        // récupère la première image
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
