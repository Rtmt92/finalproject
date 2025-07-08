<?php
namespace Src\Services;

use Src\Models\ProduitImage;

class ProduitImageService {
    private ProduitImage $model;

    public function __construct() {
        $this->model = new ProduitImage();
    }

    public function getAll(): array {
        return $this->model->getImagesByProduitId(0); // ou une méthode dédiée getAll si tu en ajoutes une
    }

    public function create(array $data): bool {
        if (!isset($data['id_produit'], $data['id_image'])) {
            return false;
        }
        return $this->model->create($data);
    }

    public function delete(int $idProduit, int $idImage): bool {
        return $this->model->delete($idProduit, $idImage);
    }

    public function detachImageFromProduct(int $idProduit, int $idImage): bool {
        return $this->model->detachImageFromProduct($idProduit, $idImage);
    }

    public function attachImageToProduct(int $idProduit, int $idImage): bool {
        return $this->model->attachImageToProduct($idProduit, $idImage);
    }
}
