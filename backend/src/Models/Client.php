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

    /**
     * Récupère tous les clients
     * @return array<int, array<string,mixed>>
     */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM client");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un client par son ID
     * @param int $id
     * @return array<string,mixed>|null
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM client WHERE id_client = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Crée un nouveau client
     * @param array{
     *   nom: string,
     *   prenom: string,
     *   email: string,
     *   numero_telephone: string,
     *   mot_de_passe: string,
     *   role: string,
     *   photo_profil?: string|null,
     *   description?: string|null
     * } $data
     * @return int|string|false  ID inséré, message d'erreur SQL ou false
     */
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
            // En développement, renvoyer l’erreur pour debug
            return 'SQL_ERROR: ' . $e->getMessage();
        }
    }

    /**
     * Met à jour un client existant
     * @param int $id
     * @param array{
     *   nom?: string,
     *   prenom?: string,
     *   email?: string,
     *   numero_telephone?: string,
     *   mot_de_passe?: string,
     *   role?: string,
     *   photo_profil?: string|null,
     *   description?: string|null
     * } $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['nom'])) {
            $fields[]        = 'nom = :nom';
            $params[':nom']  = $data['nom'];
        }
        if (isset($data['prenom'])) {
            $fields[]           = 'prenom = :prenom';
            $params[':prenom']  = $data['prenom'];
        }
        if (isset($data['email'])) {
            $fields[]           = 'email = :email';
            $params[':email']   = $data['email'];
        }
        if (isset($data['numero_telephone'])) {
            $fields[]                  = 'numero_telephone = :numero_telephone';
            $params[':numero_telephone'] = $data['numero_telephone'];
        }
        if (isset($data['mot_de_passe'])) {
            $fields[]               = 'mot_de_passe = :mot_de_passe';
            $params[':mot_de_passe'] = $data['mot_de_passe'];
        }
        if (isset($data['role'])) {
            $fields[]         = 'role = :role';
            $params[':role']  = $data['role'];
        }
        if (array_key_exists('photo_profil', $data)) {
            $fields[]                  = 'photo_profil = :photo_profil';
            $params[':photo_profil']   = $data['photo_profil'];
        }
        if (array_key_exists('description', $data)) {
            $fields[]                  = 'description = :description';
            $params[':description']    = $data['description'];
        }

        if (empty($fields)) {
            // Rien à mettre à jour
            return false;
        }

        $sql = "UPDATE client SET " . implode(', ', $fields) . " WHERE id_client = :id";
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $placeholder => $val) {
                $stmt->bindValue($placeholder, $val);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Supprime un client par son ID
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM client WHERE id_client = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Recherche un client par email
     * @param string $email
     * @return array<string,mixed>|null
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM client WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
