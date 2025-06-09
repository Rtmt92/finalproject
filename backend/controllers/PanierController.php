<?php
namespace Controllers;

use Src\Models\Panier;

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
}
