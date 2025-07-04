<?php
namespace Src\Models;

use Core\Database;
use PDO;
use PDOException;

class Client {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

        /** Récupère tous les clients sans filtre */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM client");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les clients dont nom OU prénom contient $q (insensible à la casse).
     */
    public function filterBySearch(string $q): array {
        $like = "%$q%";
        $stmt = $this->db->prepare("
            SELECT * FROM client
            WHERE nom    LIKE :q
               OR prenom LIKE :q
        ");
        $stmt->execute(['q' => $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM client WHERE id_client = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data) {
        try {
            $sql = "INSERT INTO client
                    (nom, prenom, email, numero_telephone, mot_de_passe, role, photo_profil, description)
                    VALUES
                    (:nom, :prenom, :email, :numero_telephone, :mot_de_passe, :role, :photo_profil, :description)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nom',              $data['nom']);
            $stmt->bindValue(':prenom',           $data['prenom']);
            $stmt->bindValue(':email',            $data['email']);
            $stmt->bindValue(':numero_telephone', $data['numero_telephone']);
            $stmt->bindValue(':mot_de_passe',     $data['mot_de_passe']);
            $stmt->bindValue(':role',             $data['role']);
            $stmt->bindValue(':photo_profil',     $data['photo_profil'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':description',      $data['description']  ?? null, PDO::PARAM_STR);
            $stmt->execute();

            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            return 'SQL_ERROR: ' . $e->getMessage();
        }
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];

        $allowed = [
            'nom', 'prenom', 'email', 'numero_telephone',
            'mot_de_passe', 'role', 'photo_profil', 'description'
        ];

        foreach ($allowed as $key) {
            if (!array_key_exists($key, $data)) continue;

            // Ne hash le mot de passe que s'il ne l'est pas déjà
            if ($key === 'mot_de_passe' && !password_get_info($data[$key])['algo']) {
                $data[$key] = password_hash($data[$key], PASSWORD_DEFAULT);
            }

            $fields[] = "$key = :$key";
            $params[":$key"] = $data[$key];
        }

        if (empty($fields)) return false;

        $sql = "UPDATE client SET " . implode(', ', $fields) . " WHERE id_client = :id";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM client WHERE id_client = ?");
        return $stmt->execute([$id]);
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM client WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
