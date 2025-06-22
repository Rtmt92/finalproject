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

    public function getDb(): PDO {
        return $this->db;
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM `transaction`");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `transaction` WHERE id_transaction = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int|false {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO `transaction` (montant_total, date_transaction, id_client)
                VALUES (:montant_total, :date_transaction, :id_client)
            ");
            $stmt->bindValue(':montant_total',    $data['montant_total']);
            $stmt->bindValue(':date_transaction', $data['date_transaction']);
            $stmt->bindValue(':id_client',        $data['id_client'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                return (int) $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'PDOException: ' . $e->getMessage()]);
            exit;
        }
    }

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

            if (empty($fields)) return false;

            $sql = "UPDATE `transaction` SET " . implode(', ', $fields) . " WHERE id_transaction = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

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
