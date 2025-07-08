<?php
namespace Src\Services;

use Src\Models\TransactionPanier;

class TransactionPanierService {
    private TransactionPanier $model;

    public function __construct() {
        $this->model = new TransactionPanier();
    }

    public function getAll(): array {
        return $this->model->getAll();
    }

    public function create(array $data): bool {
        if (!isset($data['id_panier'], $data['id_transaction'])) {
            return false;
        }
        return $this->model->create($data);
    }

    public function delete(int $idPanier, int $idTransaction): bool {
        return $this->model->delete($idPanier, $idTransaction);
    }

    public function deleteByPanier(int $idPanier): void {
        $this->model->deleteByPanier($idPanier);
    }

    public function deleteByTransaction(int $idTransaction): void {
        $this->model->deleteByTransaction($idTransaction);
    }
}
