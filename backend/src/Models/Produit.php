<?php
namespace Src\Models;

use Core\Database;
use PDO;
use PDOException;

class Produit {
    private PDO $db;

    public function __construct() {
        // Obtient la connexion PDO via la classe Database
        $this->db = Database::getConnection();
    }

    /**
     * Récupère tous les produits
     * @return array<int, array>
     */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM produit");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un produit par son ID
     * @param int $id
     * @return array<string,mixed>|null
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM produit WHERE id_produit = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Crée un nouveau produit
     * @param array{prix: float, description: string, id_categorie: int} $data
     * @return int|null ID du produit créé ou null en cas d'erreur
     */
    public function create(array $data): ?int {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO produit (prix, description, id_categorie) VALUES (:prix, :description, :categorie)"
            );
            $stmt->bindValue(':prix',        $data['prix']);
            $stmt->bindValue(':description', $data['description']);
            $stmt->bindValue(':categorie',   $data['id_categorie'], PDO::PARAM_INT);
            $stmt->execute();
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Met à jour un produit existant
     * @param int $id
     * @param array{prix?: float, description?: string, id_categorie?: int} $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        try {
            $fields = [];
            $params = ['id' => $id];
            if (isset($data['prix'])) {
                $fields[] = 'prix = :prix';
                $params['prix'] = $data['prix'];
            }
            if (isset($data['description'])) {
                $fields[] = 'description = :description';
                $params['description'] = $data['description'];
            }
            if (isset($data['id_categorie'])) {
                $fields[] = 'id_categorie = :categorie';
                $params['categorie'] = $data['id_categorie'];
            }
            if (empty($fields)) {
                return false;
            }
            $sql = "UPDATE produit SET " . implode(', ', $fields) . " WHERE id_produit = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Supprime un produit par son ID
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM produit WHERE id_produit = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}