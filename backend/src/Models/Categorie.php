<?php
namespace Src\Models;

use Core\Database;
use PDO;
use PDOException;

class Categorie {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Récupère toutes les catégories
     * @return array
     */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM categorie");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une catégorie par son ID
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categorie WHERE id_categorie = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Crée une nouvelle catégorie
     * @param array $data ['nom' => string]
     * @return int Nouvel ID
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO categorie (nom) VALUES (:nom)"
        );
        $stmt->execute(['nom' => $data['nom']]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Met à jour le nom d’une catégorie
     * @param int   $id
     * @param array $data ['nom' => string]
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE categorie SET nom = :nom WHERE id_categorie = :id"
        );
        return $stmt->execute([
            'nom' => $data['nom'],
            'id'  => $id
        ]);
    }

    /**
     * Supprime une catégorie par son ID
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM categorie WHERE id_categorie = :id");
        return $stmt->execute(['id' => $id]);
    }
}
