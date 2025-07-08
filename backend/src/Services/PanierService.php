<?php
namespace Src\Services;

use Src\Models\Panier;
use Core\Database;
use PDO;

class PanierService {
    private Panier $panierModel;

    public function __construct() {
        $this->panierModel = new Panier();
    }

    public function getAll(): array {
        return $this->panierModel->getAll();
    }

    public function getById(int $id): ?array {
        return $this->panierModel->getById($id);
    }

    public function create(array $data): int|false {
        if (!isset($data['prix_total'], $data['id_client'])) {
            return false;
        }
        return $this->panierModel->create([
            'prix_total' => $data['prix_total'],
            'id_client' => $data['id_client'],
        ]);
    }

    public function update(int $id, array $data): bool {
        if (!isset($data['prix_total'], $data['id_client'])) {
            return false;
        }
        $existing = $this->panierModel->getById($id);
        if (!$existing) {
            return false;
        }
        return $this->panierModel->update($id, [
            'prix_total' => $data['prix_total'],
            'id_client' => $data['id_client'],
        ]);
    }

    public function delete(int $id): bool {
        $existing = $this->panierModel->getById($id);
        if (!$existing) {
            return false;
        }
        return $this->panierModel->delete($id);
    }

    public function getWithFirstImagesByClientId(int $idClient): ?array {
        return $this->panierModel->getWithFirstImagesByClientId($idClient);
    }

    public function getWithProduitsByClientId(int $idClient): array {
        $data = $this->panierModel->getWithProduitsByClientId($idClient);
        if (!$data) {
            return ['produits' => [], 'prix_total' => 0];
        }
        return $data;
    }

    public function vider(int $idPanier): void {
        $db = Database::getConnection();

        // Supprimer les lignes dans panier_produit liées à ce panier
        $stmt = $db->prepare("DELETE FROM panier_produit WHERE id_panier = ?");
        $stmt->execute([$idPanier]);

        // Réinitialiser le prix total du panier à 0
        $stmt = $db->prepare("UPDATE panier SET prix_total = 0 WHERE id_panier = ?");
        $stmt->execute([$idPanier]);
    }
}
