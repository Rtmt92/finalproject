<?php
namespace Src\Models;

use Core\Database;
use PDO;
use PDOException;

class Message {
    private PDO $db;

    public function __construct() {
        // Obtient la connexion PDO via la classe Database
        $this->db = Database::getConnection();
    }

    /**
     * Récupère tous les messages
     * @return array<int, array>
     */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM message");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un message par son ID
     * @param int $id
     * @return array<string, mixed>|null
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM message WHERE id_message = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Crée un nouveau message
     * @param array{contenu: string, date_envoi: string, id_client: int} $data
     * @return int|null ID du message créé ou null en cas d'erreur
     */
    public function create(array $data): ?int {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO message (contenu, date_envoi, id_client) VALUES (:contenu, :date_envoi, :id_client)"
            );
            $stmt->bindValue(':contenu',    $data['contenu']);
            $stmt->bindValue(':date_envoi',  $data['date_envoi']);
            $stmt->bindValue(':id_client',   $data['id_client'], PDO::PARAM_INT);
            $stmt->execute();
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            // journaliser l'erreur si besoin
            return null;
        }
    }

    /**
     * Met à jour un message existant
     * @param int $id
     * @param array{contenu?: string, date_envoi?: string} $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        try {
            $fields = [];
            $params = ['id' => $id];
            if (isset($data['contenu'])) {
                $fields[] = 'contenu = :contenu';
                $params['contenu'] = $data['contenu'];
            }
            if (isset($data['date_envoi'])) {
                $fields[] = 'date_envoi = :date_envoi';
                $params['date_envoi'] = $data['date_envoi'];
            }
            if (empty($fields)) {
                return false;
            }
            $sql = "UPDATE message SET " . implode(', ', $fields) . " WHERE id_message = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Supprime un message par son ID
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM message WHERE id_message = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Supprime tous les messages associés à un client
     * @param int $clientId
     * @return void
     */
    public function deleteByClient(int $clientId): void {
        $stmt = $this->db->prepare("DELETE FROM message WHERE id_client = :id");
        $stmt->execute(['id' => $clientId]);
    }
}
