<?php
namespace Src\Models;

use Core\Database;
use PDO;
use PDOException;

class Panier {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Récupère tous les paniers
     * @return array
     */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM panier");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un panier par son ID
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM panier WHERE id_panier = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Crée un nouveau panier
     * @param array $data ['prix_total'=>decimal, 'id_client'=>int]
     * @return int Nouvel ID
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO panier (prix_total, id_client) VALUES (:prix_total, :id_client)"
        );
        $stmt->execute([
            'prix_total' => $data['prix_total'],
            'id_client'  => $data['id_client']
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Met à jour un panier existant
     * @param int   $id
     * @param array $data ['prix_total'=>decimal, 'id_client'=>int]
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE panier 
             SET prix_total = :prix_total, id_client = :id_client 
             WHERE id_panier = :id"
        );
        return $stmt->execute([
            'prix_total' => $data['prix_total'],
            'id_client'  => $data['id_client'],
            'id'         => $id
        ]);
    }

    /**
     * Supprime un panier par son ID
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM panier WHERE id_panier = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Supprime tous les paniers d’un client (cascade si besoin)
     * @param int $clientId
     * @return void
     */
    public function deleteByClient(int $clientId): void {
        $stmt = $this->db->prepare("DELETE FROM panier WHERE id_client = :id");
        $stmt->execute(['id' => $clientId]);
    }

    public function getWithProduitsByClientId($idClient) {
        $stmt = $this->db->prepare("
            SELECT 
                p.id_produit,
                p.nom_produit AS titre,
                p.description,
                p.prix,
                p.etat,
                p.quantite,
                i.lien AS image
            FROM 
                panier pa
            JOIN 
                panier_produit pp ON pa.id_panier = pp.id_panier
            JOIN 
                produit p ON pp.id_produit = p.id_produit
            LEFT JOIN 
                produit_image pi ON pi.id_produit = p.id_produit
            LEFT JOIN 
                image i ON i.id_image = pi.id_image
            WHERE 
                pa.id_client = :idClient
        ");
        $stmt->execute(['idClient' => $idClient]);
        $produits = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("SELECT id_panier, prix_total FROM panier WHERE id_client = :idClient");
        $stmt->execute(['idClient' => $idClient]);
        $panier = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'id_panier' => $panier['id_panier'],
            'prix_total' => $panier['prix_total'],
            'produits' => $produits
        ];
    }





}
