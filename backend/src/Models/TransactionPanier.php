<?php
namespace Src\Models;

use Core\Database;
use PDO;
use PDOException;

class TransactionPanier {
    private PDO $db;

    public function __construct() {
        // Obtient la connexion PDO via la classe Database
        $this->db = Database::getConnection();
    }

    /**
     * Récupère toutes les liaisons panier-transaction
     * @return array<int, array>
     */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM transaction_panier");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée une liaison entre un panier et une transaction
     * @param array{ id_panier: int, id_transaction: int } $data
     * @return bool
     */
    public function create(array $data): bool {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO transaction_panier (id_panier, id_transaction) VALUES (:panier, :transaction)"
            );
            $stmt->bindValue(':panier',     $data['id_panier'],     PDO::PARAM_INT);
            $stmt->bindValue(':transaction',$data['id_transaction'],PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Optionnel : journaliser l'erreur
            return false;
        }
    }

    /**
     * Supprime une liaison spécifique entre panier et transaction
     * @param int $panierId
     * @param int $transactionId
     * @return bool
     */
    public function delete(int $panierId, int $transactionId): bool {
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM transaction_panier WHERE id_panier = :panier AND id_transaction = :transaction"
            );
            $stmt->bindValue(':panier',     $panierId,      PDO::PARAM_INT);
            $stmt->bindValue(':transaction',$transactionId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Optionnel : journaliser l'erreur
            return false;
        }
    }

    /**
     * Supprime toutes les liaisons pour un panier donné
     * @param int $panierId
     * @return void
     */
    public function deleteByPanier(int $panierId): void {
        $stmt = $this->db->prepare(
            "DELETE FROM transaction_panier WHERE id_panier = :id"
        );
        $stmt->execute(['id' => $panierId]);
    }

    /**
     * Supprime toutes les liaisons pour une transaction donnée
     * @param int $transactionId
     * @return void
     */
    public function deleteByTransaction(int $transactionId): void {
        $stmt = $this->db->prepare(
            "DELETE FROM transaction_panier WHERE id_transaction = :id"
        );
        $stmt->execute(['id' => $transactionId]);
    }
}
