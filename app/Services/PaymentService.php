<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class PaymentService
{
    public function remainingItems($transaction): Collection
    {
        throw new \LogicException(__METHOD__.' belum diimplementasi');
    }

    public function pay(
        $transaction,
        array $items,
        string $method,
        User $cashier,
        ?int $cashReceived = null,
        ?string $payerName = null
    ) {
        throw new \LogicException(__METHOD__.' belum diimplementasi');
    }

    public function payRemaining(
        $transaction,
        string $method,
        User $cashier,
        ?int $cashReceived = null,
        ?string $payerName = null
    ) {
        throw new \LogicException(__METHOD__.' belum diimplementasi');
    }
}