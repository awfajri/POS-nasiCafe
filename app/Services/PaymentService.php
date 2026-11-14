<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class PaymentService
{
    public function remainingItems($transaction): Collection
    {
        throw new \LogicException('Belum diimplementasi');
    }

    public function pay(
        $transaction,
        array $items,
        string $method,
        User $cashier,
        ?int $cashReceived = null,
        ?string $payerName = null
    ) {
        throw new \LogicException('Belum diimplementasi');
    }

    public function payRemaining(
        $transaction,
        string $method,
        User $cashier,
        ?int $cashReceived = null,
        ?string $payerName = null
    ) {
        throw new \LogicException('Belum diimplementasi');
    }
}
