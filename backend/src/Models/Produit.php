<?php
// src/Models/Produit.php
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
        return $this->db->query("SELECT * FROM produit")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM produit WHERE id_produit = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): ?int {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO produit 
                    (nom_produit, prix, description, id_categorie, quantite, etat)
                 VALUES 
                    (:nom, :prix, :description, :categorie, :quantite, :etat)"
            );
            $stmt->execute([
                'nom'         => $data['nom_produit'],
                'prix'        => $data['prix'],
                'description' => $data['description'],
                'categorie'   => $data['id_categorie'],
                'quantite'    => $data['quantite'],
                'etat'        => $data['etat']
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            return null;
        }
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = ['id' => $id];

        if (array_key_exists('nom_produit', $data)) {
            $fields[] = 'nom_produit = :nom_produit';
            $params['nom_produit'] = $data['nom_produit'];
        }
        if (array_key_exists('prix', $data)) {
            $fields[] = 'prix = :prix';
            $params['prix'] = $data['prix'];
        }
        if (array_key_exists('description', $data)) {
            $fields[] = 'description = :description';
            $params['description'] = $data['description'];
        }
        if (array_key_exists('id_categorie', $data)) {
            $fields[] = 'id_categorie = :id_categorie';
            $params['id_categorie'] = $data['id_categorie'];
        }
        if (array_key_exists('quantite', $data)) {
            $fields[] = 'quantite = :quantite';
            $params['quantite'] = $data['quantite'];
        }
        if (array_key_exists('etat', $data)) {
            $fields[] = 'etat = :etat';
            $params['etat'] = $data['etat'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE produit SET " . implode(', ', $fields) . " WHERE id_produit = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function destroy(int $id): bool {
        try {
            $db = Database::getConnection();
            $db->prepare("DELETE FROM panier_produit WHERE id_produit = :id")
               ->execute(['id' => $id]);
            $db->prepare("DELETE FROM produit_image WHERE id_produit = :id")
               ->execute(['id' => $id]);
            return $db->prepare("DELETE FROM produit WHERE id_produit = :id")
                      ->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur suppression produit : " . $e->getMessage());
            return false;
        }
    }

    public function findRandom(): ?array {
        $stmt = $this->db->query("SELECT * FROM produit ORDER BY RAND() LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function filter($cat = null, $etat = null, $search = null): array {
        $sql    = "SELECT * FROM produit WHERE 1=1";
        $params = [];

        if ($cat !== null) {
            $sql    .= " AND id_categorie = ?";
            $params[] = $cat;
        }
        if ($etat !== null) {
            $sql    .= " AND etat = ?";
            $params[] = $etat;
        }
        if ($search !== null) {
            $sql    .= " AND (nom_produit LIKE ? OR description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
