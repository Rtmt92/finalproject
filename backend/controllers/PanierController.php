<?php
namespace Controllers;

use Src\Models\Panier;
use Core\Database;


class PanierController {
    private Panier $panierModel;

    public function __construct() {
        $this->panierModel = new Panier();
    }

    /** GET /panier */
    public function index(): void {
        $all = $this->panierModel->getAll();
        header('Content-Type: application/json');
        echo json_encode($all);
    }

    /** GET /panier/{id} */
    public function show(int $id): void {
        $item = $this->panierModel->getById($id);
        if (!$item) {
            http_response_code(404);
            echo json_encode(['error' => 'Panier non trouvé']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($item);
    }

    /** POST /panier */
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (
            !isset($data['prix_total']) ||
            !isset($data['id_client'])
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        $newId = $this->panierModel->create([
            'prix_total' => $data['prix_total'],
            'id_client'  => $data['id_client']
        ]);
        http_response_code(201);
        echo json_encode(['message' => 'Panier créé', 'id_panier' => $newId]);
    }

    /** PUT/PATCH /panier/{id} */
    public function update(int $id): void {
        $existing = $this->panierModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Panier non trouvé']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (
            !isset($data['prix_total']) ||
            !isset($data['id_client'])
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        $ok = $this->panierModel->update($id, [
            'prix_total' => $data['prix_total'],
            'id_client'  => $data['id_client']
        ]);
        if ($ok) {
            echo json_encode(['message' => 'Panier mis à jour']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de mettre à jour']);
        }
    }

    /** DELETE /panier/{id} */
    public function destroy(int $id): void {
        $existing = $this->panierModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Panier non trouvé']);
            return;
        }
        $ok = $this->panierModel->delete($id);
        if ($ok) {
            echo json_encode(['message' => 'Panier supprimé']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de supprimer']);
        }
    }

    public function showUserPanier(): void {
        session_start();
        if (!isset($_SESSION['id_client'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Utilisateur non connecté']);
            return;
        }

        $idClient = $_SESSION['id_client'];
        $result = $this->panierModel->getPanierWithProduitsByClientId($idClient);

        if (!$result) {
            echo json_encode(['id_panier' => null, 'prix_total' => 0, 'produits' => []]);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function getMyPanier() {
        session_start();
        if (!isset($_SESSION['id_client'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non connecté']);
            return;
        }

        $data = $this->panierModel->getWithProduitsByClientId($_SESSION['id_client']);
        if (!$data) {
            echo json_encode(['produits' => [], 'prix_total' => 0]);
        } else {
            echo json_encode($data);
        }
    }

    public function vider(int $id_panier): void {
        $db = \Core\Database::getConnection();

        // Supprimer les lignes dans panier_produit liées à ce panier
        $stmt = $db->prepare("DELETE FROM panier_produit WHERE id_panier = ?");
        $stmt->execute([$id_panier]);

        // Réinitialiser le prix total du panier à 0
        $stmt = $db->prepare("UPDATE panier SET prix_total = 0 WHERE id_panier = ?");
        $stmt->execute([$id_panier]);

        echo json_encode(['message' => 'Panier vidé avec succès']);
    }



    
}
