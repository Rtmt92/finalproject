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
     * @return array
     */
    public function getAll(): array {
        $stmt = $this->db->prepare("SELECT * FROM client");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupère un client par son identifiant
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM client WHERE id_client = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $client = $stmt->fetch();
        return $client ?: null;
    }

    /**
     * Crée un nouveau client
     * @param array $data  (doit contenir nom, prenom, email, numero_telephone, mot_de_passe, role)
     * @return int|false  retourne l'ID créé ou false en cas d'erreur
     */
    public function create(array $data) {
        try {
            $sql = "INSERT INTO client (nom, prenom, email, numero_telephone, mot_de_passe, role)
                    VALUES (:nom, :prenom, :email, :numero_telephone, :mot_de_passe, :role)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nom',             $data['nom']);
            $stmt->bindValue(':prenom',          $data['prenom']);
            $stmt->bindValue(':email',           $data['email']);
            $stmt->bindValue(':numero_telephone',$data['numero_telephone']);
            $stmt->bindValue(':mot_de_passe',    $data['mot_de_passe']);
            $stmt->bindValue(':role',            $data['role']);
            $stmt->execute();
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            // Optionnel : logger l’erreur
            return false;
        }
    }

    /**
     * Met à jour un client existant (ne modifie que les champs passés dans $data)
     * @param int   $id
     * @param array $data  (clés possibles : nom, prenom, email, numero_telephone, mot_de_passe, role)
     * @return bool
     */
    public function update(int $id, array $data): bool {
        // Construction dynamique du SQL en fonction de ce qui est passé
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['nom'])) {
            $fields[]         = 'nom = :nom';
            $params[':nom']   = $data['nom'];
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
            // Optionnel : logger l’erreur
            return false;
        }
    }

    /**
     * Supprime un client par son ID
     * @param int $id
     * @return bool
     */
    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM client WHERE id_client = :id");
        $stmt->execute(['id' => $id]);
    }

}
