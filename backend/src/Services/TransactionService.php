<?php
namespace Src\Services;

use Src\Models\Transaction;

class TransactionService {
    private Transaction $transactionModel;

    public function __construct() {
        $this->transactionModel = new Transaction();
    }

    public function getAll(): array {
        return $this->transactionModel->getAll();
    }

    public function getById(int $id): ?array {
        return $this->transactionModel->getById($id);
    }

    public function create(array $data): int|false {
        if (empty($data['montant_total']) || empty($data['date_transaction']) || empty($data['id_client'])) {
            return false;
        }
        return $this->transactionModel->create($data);
    }

    public function update(int $id, array $data): bool {
        $payload = [];
        if (isset($data['montant_total'])) {
            $payload['montant_total'] = $data['montant_total'];
        }
        if (isset($data['date_transaction'])) {
            $payload['date_transaction'] = $data['date_transaction'];
        }
        if (isset($data['id_client'])) {
            $payload['id_client'] = $data['id_client'];
        }
        if (empty($payload)) {
            return false;
        }
        return $this->transactionModel->update($id, $payload);
    }

    public function delete(int $id): bool {
        return $this->transactionModel->delete($id);
    }
}
