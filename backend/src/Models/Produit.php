<?php
namespace Src\Models;

use Core\Database;
use PDO;
use PDOException;

class Produit {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM produit");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM produit WHERE id_produit = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): ?int {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO produit 
                    (nom_produit, prix, description, id_categorie)
                 VALUES 
                    (:nom,       :prix, :description, :categorie)"
            );
            $stmt->bindValue(':nom',         $data['nom_produit']);
            $stmt->bindValue(':prix',        $data['prix']);
            $stmt->bindValue(':description', $data['description']);
            $stmt->bindValue(':categorie',   $data['id_categorie'], PDO::PARAM_INT);
            $stmt->execute();
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            return null;
        }
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['nom_produit'])) {
            $fields[]               = 'nom_produit = :nom';
            $params['nom']          = $data['nom_produit'];
        }
        if (isset($data['prix'])) {
            $fields[]               = 'prix = :prix';
            $params['prix']         = $data['prix'];
        }
        if (isset($data['description'])) {
            $fields[]                    = 'description = :description';
            $params['description']       = $data['description'];
        }
        if (isset($data['id_categorie'])) {
            $fields[]                     = 'id_categorie = :categorie';
            $params['categorie']          = $data['id_categorie'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql  = "UPDATE produit SET " . implode(', ', $fields) . " WHERE id_produit = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM produit WHERE id_produit = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function findRandom(): ?array {
        $stmt = $this->db->query("SELECT * FROM produit ORDER BY RAND() LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
