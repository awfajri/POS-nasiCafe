<?php

namespace App\Services;

use App\Models\User;

class TransactionService
{
    public function createOrder(
        array $items,
        string $customerName,
        ?string $tableNumber,
        string $source,
        ?User $customer = null,
        ?User $cashier = null
    ) {
        throw new \LogicException(__METHOD__.' belum diimplementasi');
    }

    public function changeStatus($transaction, string $to, ?User $actor = null)
    {
        throw new \LogicException(__METHOD__.' belum diimplementasi');
    }

    public function allowedTransitions($transaction): array
    {
        throw new \LogicException(__METHOD__.' belum diimplementasi');
    }

    public function moveToProcessing($transaction, ?User $actor = null): void
    {
        throw new \LogicException(__METHOD__.' belum diimplementasi');
    }

    public function deductStock($transaction): void
    {
        throw new \LogicException(__METHOD__.' belum diimplementasi');
    }

    public function restoreStock($transaction): void
    {
        throw new \LogicException(__METHOD__.' belum diimplementasi');
    }

    public function cancelExpired(): int
    {
        throw new \LogicException(__METHOD__.' belum diimplementasi');
    }
}