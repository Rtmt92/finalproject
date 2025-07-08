<?php
namespace Src\Services;

use Src\Models\Image;

class ImageService {
    private Image $imageModel;

    public function __construct() {
        $this->imageModel = new Image();
    }

    public function getAllImages(): array {
        // Ici on récupère toutes les images sans filtre
        // Ton modèle ne semble pas avoir cette méthode, on peut la créer si besoin
        // Sinon à adapter selon ta base
        $stmt = $this->imageModel->db->query("SELECT * FROM image");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getImageById(int $id): ?array {
        return $this->imageModel->getById($id);
    }

    public function createImage(array $data) {
        if (empty($data['lien'])) {
            return false;
        }
        return $this->imageModel->create($data['lien']);
    }

    public function updateImage(int $id, array $data): bool {
        $existing = $this->imageModel->getById($id);
        if (!$existing || empty($data['lien'])) {
            return false;
        }
        // Pas de méthode update dans ton modèle, on peut la créer
        $stmt = $this->imageModel->db->prepare("UPDATE image SET lien = :lien WHERE id_image = :id");
        return $stmt->execute(['lien' => $data['lien'], 'id' => $id]);
    }

    public function deleteImage(int $id): bool {
        $existing = $this->imageModel->getById($id);
        if (!$existing) {
            return false;
        }
        // Vérifier si image est utilisée ailleurs ? (optionnel)
        if ($this->imageModel->isUsedElsewhere($id)) {
            return false; // ou gérer selon besoin
        }
        return $this->imageModel->delete($id);
    }
}
