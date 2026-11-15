<?php

namespace App\Services;

use App\Models\TransactionPayment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentService
{
    public function remainingItems($transaction): Collection
    {
        $transactionItems = method_exists($transaction, 'items')
            ? $transaction->items
            : ($transaction->items ?? collect());

        $payments = TransactionPayment::where('transaction_id', $transaction->id)->get();

        $paidQtyMap = [];
        foreach ($payments as $payment) {
            foreach ($payment->paid_items_json ?? [] as $paidItem) {
                $itemId = (int) ($paidItem['transaction_item_id'] ?? $paidItem['id'] ?? 0);
                $qty = (int) ($paidItem['qty'] ?? $paidItem['quantity'] ?? 0);

                if ($itemId > 0) {
                    $paidQtyMap[$itemId] = ($paidQtyMap[$itemId] ?? 0) + $qty;
                }
            }
        }

        return collect($transactionItems)
            ->map(function ($item) use ($paidQtyMap) {
                $orderedQty = (int) ($item->qty ?? $item->quantity ?? 0);
                $paidQty = $paidQtyMap[$item->id] ?? 0;
                $remainingQty = max(0, $orderedQty - $paidQty);

                $unitPrice = (int) ($item->price ?? $item->unit_price ?? (
                    $orderedQty > 0 ? intdiv((int) ($item->subtotal ?? 0), $orderedQty) : 0
                ));

                $item->remaining_qty = $remainingQty;
                $item->remaining_subtotal = $remainingQty * $unitPrice;

                return $item;
            })
            ->filter(fn ($item) => $item->remaining_qty > 0)
            ->values();
    }

    public function pay(
        $transaction,
        array $items,
        string $method,
        User $cashier,
        ?int $cashReceived = null,
        ?string $payerName = null
    ) {
        if (! in_array($method, ['cash', 'qris'], true)) {
            throw new InvalidArgumentException('Metode pembayaran harus cash atau qris.');
        }

        if (empty($items)) {
            throw new InvalidArgumentException('Minimal harus ada 1 item yang dibayar.');
        }

        return DB::transaction(function () use ($transaction, $items, $method, $cashier, $cashReceived, $payerName) {
            $remainingMap = $this->remainingItems($transaction)->keyBy('id');
            $normalizedItems = [];
            $amountPaid = 0;

            foreach ($items as $item) {
                $itemId = (int) ($item['transaction_item_id'] ?? $item['id'] ?? 0);
                $qty = (int) ($item['qty'] ?? $item['quantity'] ?? 0);

                if ($itemId <= 0 || $qty <= 0) {
                    throw new InvalidArgumentException('Item pembayaran dan jumlah qty harus valid.');
                }

                $remainingItem = $remainingMap->get($itemId);
                if ($remainingItem && $qty > $remainingItem->remaining_qty) {
                    throw new InvalidArgumentException("Jumlah qty item #{$itemId} melebihi sisa yang belum dibayar.");
                }

                if (isset($item['subtotal'])) {
                    $subtotal = (int) $item['subtotal'];
                } elseif ($remainingItem) {
                    $unitPrice = (int) ($remainingItem->price ?? $remainingItem->unit_price ?? 0);
                    $subtotal = $unitPrice * $qty;
                } else {
                    $subtotal = (int) ($item['price'] ?? 0) * $qty;
                }

                $amountPaid += $subtotal;
                $normalizedItems[] = [
                    'transaction_item_id' => $itemId,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            if ($amountPaid <= 0) {
                throw new InvalidArgumentException('Nominal pembayaran harus lebih dari Rp 0.');
            }

            if ($method === 'cash') {
                if ($cashReceived === null || $cashReceived < $amountPaid) {
                    throw new InvalidArgumentException('Uang tunai yang diterima kurang dari nominal pembayaran.');
                }

                $changeAmount = $cashReceived - $amountPaid;
            } else {
                $cashReceived = null;
                $changeAmount = 0;
            }

            $payment = TransactionPayment::create([
                'transaction_id' => $transaction->id,
                'cashier_id' => $cashier->id,
                'payer_name' => $payerName,
                'payment_method' => $method,
                'amount_paid' => $amountPaid,
                'cash_received' => $cashReceived,
                'change_amount' => $changeAmount,
                'paid_items_json' => $normalizedItems,
            ]);

            if ($this->remainingItems($transaction)->isEmpty()) {
                $attributes = $transaction->getAttributes();
                if (array_key_exists('status', $attributes)) {
                    $transaction->update(['status' => 'paid']);
                } elseif (array_key_exists('payment_status', $attributes)) {
                    $transaction->update(['payment_status' => 'paid']);
                }
            }

            return $payment;
        });
    }

    public function payRemaining(
        $transaction,
        string $method,
        User $cashier,
        ?int $cashReceived = null,
        ?string $payerName = null
    ) {
        $remainingItems = $this->remainingItems($transaction);

        if ($remainingItems->isEmpty()) {
            throw new InvalidArgumentException('Tidak ada sisa item yang perlu dibayar.');
        }

        $itemsPayload = $remainingItems->map(fn ($item) => [
            'transaction_item_id' => $item->id,
            'qty' => $item->remaining_qty,
            'subtotal' => $item->remaining_subtotal,
        ])->all();

        return $this->pay($transaction, $itemsPayload, $method, $cashier, $cashReceived, $payerName);
    }
}