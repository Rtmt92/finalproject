<?php
namespace Src\Services;

use Src\Models\Produit;
use Src\Models\ProduitImage;
use Src\Models\Image;

class ProduitService {
    private Produit $produitModel;
    private ProduitImage $prodImageModel;
    private Image $imageModel;

    public function __construct() {
        $this->produitModel   = new Produit();
        $this->prodImageModel = new ProduitImage();
        $this->imageModel     = new Image();
    }

    public function filter($categorie = null, $etat = null, $search = null): array {
        return $this->produitModel->filter($categorie, $etat, $search);
    }

    public function getById(int $id): ?array {
        return $this->produitModel->getById($id);
    }

    public function getFirstImageUrl(int $idProduit): ?string {
        $pivots = $this->prodImageModel->getImagesByProduitId($idProduit);
        if (!empty($pivots[0]['id_image'])) {
            $img = $this->imageModel->getById((int)$pivots[0]['id_image']);
            return $img['lien'] ?? null;
        }
        return null;
    }

    public function getAllWithImages(?int $categorie = null, ?string $etat = null, ?string $search = null): array {
        $produits = $this->filter($categorie, $etat, $search);
        $results = [];
        foreach ($produits as $p) {
            $imageUrl = $this->getFirstImageUrl((int)$p['id_produit']);
            $results[] = [
                'id'          => $p['id_produit'],
                'titre'       => $p['nom_produit'],
                'description' => $p['description'],
                'prix'        => $p['prix'],
                'etat'        => $p['etat'] ?? '',
                'quantite'    => $p['quantite'] ?? 0,
                'image'       => $imageUrl,
            ];
        }
        return $results;
    }

    public function getAll(): array {
        return $this->produitModel->getAll();
    }

    public function create(array $data): ?int {
        return $this->produitModel->create($data);
    }

    public function update(int $id, array $data): bool {
        return $this->produitModel->update($id, $data);
    }

    public function destroy(int $id): bool {
        return $this->produitModel->destroy($id);
    }

    public function getImagesByProduitId(int $idProduit): array {
        $pivots = $this->prodImageModel->getImagesByProduitId($idProduit);
        $images = [];
        foreach ($pivots as $pv) {
            $img = $this->imageModel->getById((int)$pv['id_image']);
            if ($img) {
                $images[] = $img;
            }
        }
        return $images;
    }

    public function detachImageFromProduct(int $idProduit, int $idImage): bool {
        $ok = $this->prodImageModel->detachImageFromProduct($idProduit, $idImage);
        if ($ok && !$this->imageModel->isUsedElsewhere($idImage)) {
            $this->imageModel->delete($idImage);
        }
        return $ok;
    }

    public function uploadImage(int $idProduit, array $file, string $uploadDir, string $publicUrl): ?array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('', true) . '.' . $ext;
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return null;
        }

        $publicLink = rtrim($publicUrl, '/') . '/' . $fileName;

        $idImage = $this->imageModel->create(['lien' => $publicLink]);
        $this->prodImageModel->create([
            'id_produit' => $idProduit,
            'id_image' => $idImage,
        ]);

        return [
            'id_image' => $idImage,
            'url' => $publicLink,
        ];
    }

    public function findRandom(): ?array {
        return $this->produitModel->findRandom();
    }
}
