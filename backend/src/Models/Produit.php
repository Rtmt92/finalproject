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

    public function getByCategorie(int $id): array {
        $stmt = $this->db->prepare("SELECT * FROM produit WHERE id_categorie = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEtat(string $etat): array {
        $stmt = $this->db->prepare("SELECT * FROM produit WHERE etat = :etat");
        $stmt->execute(['etat' => $etat]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCategorieAndEtat(int $categorie, string $etat): array {
        $stmt = $this->db->prepare("SELECT * FROM produit WHERE id_categorie = :cat AND etat = :etat");
        $stmt->execute(['cat' => $categorie, 'etat' => $etat]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): ?int {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO produit 
                    (nom_produit, prix, description, id_categorie, quantite, etat)
                VALUES 
                    (:nom, :prix, :description, :categorie, :quantite, :etat)"
            );
            $stmt->bindValue(':nom',         $data['nom_produit']);
            $stmt->bindValue(':prix',        $data['prix']);
            $stmt->bindValue(':description', $data['description']);
            $stmt->bindValue(':categorie',   $data['id_categorie'], PDO::PARAM_INT);
            $stmt->bindValue(':quantite',    $data['quantite'], PDO::PARAM_INT);
            $stmt->bindValue(':etat',        $data['etat']);
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
            $fields[] = 'nom_produit = :nom_produit';
            $params['nom_produit'] = $data['nom_produit'];
        }

        if (isset($data['prix'])) {
            $fields[] = 'prix = :prix';
            $params['prix'] = $data['prix'];
        }

        if (isset($data['description'])) {
            $fields[] = 'description = :description';
            $params['description'] = $data['description'];
        }

        if (isset($data['id_categorie'])) {
            $fields[] = 'id_categorie = :id_categorie';
            $params['id_categorie'] = $data['id_categorie'];
        }

        if (isset($data['quantite'])) {
            $fields[] = 'quantite = :quantite';
            $params['quantite'] = $data['quantite'];
        }

        if (isset($data['etat'])) {
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
        $db = \Core\Database::getConnection();

        // Supprimer les liaisons liées au produit
        $db->prepare("DELETE FROM panier_produit WHERE id_produit = :id")
           ->execute(['id' => $id]);

        $db->prepare("DELETE FROM produit_image WHERE id_produit = :id")
           ->execute(['id' => $id]);

        // Supprimer le produit lui-même
        $stmt = $db->prepare("DELETE FROM produit WHERE id_produit = :id");
        return $stmt->execute(['id' => $id]);

    } catch (\PDOException $e) {
        error_log("Erreur suppression produit : " . $e->getMessage());
        return false;
    }
}


    // public function delete(int $id): bool {
    //     try {
    //         $stmt = $this->db->prepare("DELETE FROM produit WHERE id_produit = :id");
    //         $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    //         return $stmt->execute();
    //     } catch (PDOException $e) {
    //         return false;
    //     }
    // }

    public function findRandom(): ?array {
        $stmt = $this->db->query("SELECT * FROM produit ORDER BY RAND() LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}


