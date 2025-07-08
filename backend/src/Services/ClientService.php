<?php
namespace Src\Services;

use Src\Models\Client;
use Src\Models\Message;
use Src\Models\Signaler;
use Src\Models\Panier;
use PDOException;

class ClientService {
    private Client $clientModel;
    private Message $messageModel;
    private Signaler $signalerModel;
    private Panier $panierModel;

    public function __construct() {
        $this->clientModel = new Client();
        $this->messageModel = new Message();
        $this->signalerModel = new Signaler();
        $this->panierModel = new Panier();
    }

    public function getAllClients(string $search = ''): array {
        if ($search !== '') {
            return $this->clientModel->filterBySearch($search);
        }
        return $this->clientModel->getAll();
    }

    public function getClientById(int $id): ?array {
        return $this->clientModel->getById($id);
    }

    public function createClient(array $data) {
        // Validation basique ici (peut être étendue)
        $requiredFields = ['nom', 'prenom', 'email', 'numero_telephone', 'mot_de_passe'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        // Hash mot de passe
        $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);

        return $this->clientModel->create([
            'nom'              => $data['nom'],
            'prenom'           => $data['prenom'],
            'email'            => $data['email'],
            'numero_telephone' => $data['numero_telephone'],
            'mot_de_passe'     => $data['mot_de_passe'],
            'role'             => $data['role'] ?? 'client',
            'photo_profil'     => $data['photo_profil'] ?? null,
            'description'      => $data['description'] ?? null,
        ]);
    }

    public function updateClient(int $id, array $data): bool {
        $existing = $this->clientModel->getById($id);
        if (!$existing) {
            return false;
        }
        return $this->clientModel->update($id, $data);
    }

    public function deleteClient(int $id): bool {
        $existing = $this->clientModel->getById($id);
        if (!$existing) {
            return false;
        }
        $this->messageModel->deleteByClient($id);
        $this->signalerModel->deleteByClient($id);
        $this->panierModel->deleteByClient($id);

        try {
            return $this->clientModel->delete($id);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updatePassword(int $id, string $ancien, string $nouveau, string $confirmation): bool|string {
        if ($nouveau !== $confirmation) {
            return 'Les mots de passe ne correspondent pas';
        }

        $client = $this->clientModel->getById($id);
        if (!$client || !password_verify($ancien, $client['mot_de_passe'])) {
            return 'Mot de passe actuel incorrect';
        }

        $success = $this->clientModel->update($id, ['mot_de_passe' => $nouveau]);
        if ($success) {
            return true;
        }
        return 'Erreur lors de la mise à jour';
    }
}
