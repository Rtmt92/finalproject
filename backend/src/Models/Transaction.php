<?php
namespace Src\Models;

use Core\Database;
use PDO;
use PDOException;

class Transaction {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Récupère toutes les transactions
     * @return array
     */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM `transaction`");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une transaction par son ID
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `transaction` WHERE id_transaction = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Crée une nouvelle transaction
     * @param array $data ['montant_total','date_transaction','id_client']
     * @return bool
     */
    public function create(array $data): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO `transaction` (montant_total, date_transaction, id_client)
                VALUES (:montant_total, :date_transaction, :id_client)
            ");
            $stmt->bindValue(':montant_total',   $data['montant_total']);
            $stmt->bindValue(':date_transaction', $data['date_transaction']);
            $stmt->bindValue(':id_client',        $data['id_client'], PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Vous pouvez logger $e->getMessage() ici si besoin
            return false;
        }
    }

    /**
     * Met à jour une transaction existante
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        try {
            $fields = [];
            $params = ['id' => $id];

            if (isset($data['montant_total'])) {
                $fields[] = 'montant_total = :montant_total';
                $params['montant_total'] = $data['montant_total'];
            }
            if (isset($data['date_transaction'])) {
                $fields[] = 'date_transaction = :date_transaction';
                $params['date_transaction'] = $data['date_transaction'];
            }
            if (isset($data['id_client'])) {
                $fields[] = 'id_client = :id_client';
                $params['id_client'] = $data['id_client'];
            }

            if (empty($fields)) {
                // Rien à mettre à jour
                return false;
            }

            $sql = "UPDATE `transaction` SET " . implode(', ', $fields) . " WHERE id_transaction = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Supprime une transaction
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM `transaction` WHERE id_transaction = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
