<?php
namespace Src\Services;

use Src\Models\Categorie;

class CategorieService {
    private Categorie $categorieModel;

    public function __construct() {
        $this->categorieModel = new Categorie();
    }

    public function getAllCategories(): array {
        return $this->categorieModel->getAll();
    }

    public function getCategoryById(int $id): ?array {
        return $this->categorieModel->getById($id);
    }

    /**
     * @param array $data
     * @return int|false Retourne l'ID créé ou false si erreur
     */
    public function createCategory(array $data) {
        if (empty($data['nom'])) {
            return false; // ou lancer une exception selon ta gestion d'erreur
        }
        return $this->categorieModel->create(['nom' => $data['nom']]);
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool succès ou échec
     */
    public function updateCategory(int $id, array $data): bool {
        $existing = $this->categorieModel->getById($id);
        if (!$existing || empty($data['nom'])) {
            return false;
        }
        return $this->categorieModel->update($id, ['nom' => $data['nom']]);
    }

    /**
     * @param int $id
     * @return bool succès ou échec
     */
    public function deleteCategory(int $id): bool {
        $existing = $this->categorieModel->getById($id);
        if (!$existing) {
            return false;
        }
        return $this->categorieModel->delete($id);
    }
}
