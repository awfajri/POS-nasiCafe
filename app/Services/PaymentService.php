<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionPayment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        protected ?TransactionService $transactionService = null
    ) {}

    /**
     * Daftar detail item yang masih memiliki sisa porsi belum dibayar.
     */
    public function remainingItems(Transaction $transaction): Collection
    {
        return $transaction->details()
            ->with('menu')
            ->orderBy('id')
            ->get()
            ->map(function (TransactionDetail $detail): TransactionDetail {
                $detail->remaining_subtotal = $detail->remaining_quantity * $detail->unit_price;

                return $detail;
            })
            ->filter(fn (TransactionDetail $detail): bool => $detail->remaining_quantity > 0)
            ->values();
    }

    /**
     * Memproses pembayaran (penuh maupun split bill per item).
     *
     * @param  array<int, array{detail_id: int|string, qty: int|string}>  $items
     */
    public function pay(
        Transaction $transaction,
        array $items,
        string $method,
        User $cashier,
        ?int $cashReceived = null,
        ?string $payerName = null
    ): TransactionPayment {
        if (! in_array($method, ['cash', 'qris'], true)) {
            throw ValidationException::withMessages([
                'payment_method' => 'Metode pembayaran harus cash atau qris.',
            ]);
        }

        $mergedItems = [];
        foreach ($items as $item) {
            $detailId = (int) ($item['detail_id'] ?? 0);
            $qty = (int) ($item['qty'] ?? 0);

            if ($detailId < 1 || $qty < 1) {
                throw ValidationException::withMessages([
                    'items' => 'Item pembayaran dan jumlah porsi harus valid.',
                ]);
            }

            $mergedItems[$detailId] = ($mergedItems[$detailId] ?? 0) + $qty;
        }

        if ($mergedItems === []) {
            throw ValidationException::withMessages([
                'items' => 'Minimal harus ada 1 item yang dibayar.',
            ]);
        }

        return DB::transaction(function () use ($transaction, $mergedItems, $method, $cashier, $cashReceived, $payerName): TransactionPayment {
            $lockedTransaction = Transaction::query()
                ->lockForUpdate()
                ->findOrFail($transaction->getKey());

            if (! $lockedTransaction->canBePaid()) {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi ini sudah lunas atau tidak dapat dibayar.',
                ]);
            }

            $lockedDetails = $lockedTransaction->details()
                ->with('menu')
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $paidItemsJson = [];
            $amountPaid = 0;

            foreach ($mergedItems as $detailId => $qty) {
                /** @var TransactionDetail|null $detail */
                $detail = $lockedDetails->get($detailId);

                if (! $detail) {
                    throw ValidationException::withMessages([
                        'items' => "Detail item #{$detailId} tidak ditemukan pada transaksi ini.",
                    ]);
                }

                $menuName = $detail->menu?->name ?? "Menu #{$detail->menu_id}";
                $remainingQty = $detail->remaining_quantity;

                if ($qty > $remainingQty) {
                    throw ValidationException::withMessages([
                        'items' => "Jumlah porsi {$menuName} melebihi sisa yang belum dibayar (sisa: {$remainingQty}).",
                    ]);
                }

                $subtotal = $detail->unit_price * $qty;

                $detail->update([
                    'paid_quantity' => $detail->paid_quantity + $qty,
                ]);

                $amountPaid += $subtotal;
                $paidItemsJson[] = [
                    'detail_id' => $detail->id,
                    'menu_name' => $menuName,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            if ($method === 'cash') {
                if ($cashReceived === null || $cashReceived < $amountPaid) {
                    throw ValidationException::withMessages([
                        'cash_received' => 'Uang tunai yang diterima kurang dari nominal tagihan (Rp ' . number_format($amountPaid, 0, ',', '.') . ').',
                    ]);
                }

                $changeAmount = $cashReceived - $amountPaid;
            } else {
                $cashReceived = null;
                $changeAmount = 0;
            }

            $payment = TransactionPayment::create([
                'transaction_id' => $lockedTransaction->id,
                'cashier_id' => $cashier->id,
                'payer_name' => $payerName,
                'payment_method' => $method,
                'amount_paid' => $amountPaid,
                'cash_received' => $cashReceived,
                'change_amount' => $changeAmount,
                'paid_items_json' => $paidItemsJson,
            ]);

            $newPaidAmount = $lockedTransaction->paid_amount + $amountPaid;
            $allDetailsPaid = $lockedDetails->every(
                fn (TransactionDetail $detail): bool => $detail->paid_quantity >= $detail->quantity
            );
            $isFullyPaid = $allDetailsPaid || $newPaidAmount >= $lockedTransaction->total_amount;

            $lockedTransaction->update([
                'cashier_id' => $lockedTransaction->cashier_id ?? $cashier->id,
                'paid_amount' => $newPaidAmount,
                'payment_status' => $isFullyPaid
                    ? Transaction::PAYMENT_PAID
                    : Transaction::PAYMENT_PARTIAL,
                'expires_at' => null,
            ]);

            if ($lockedTransaction->status === Transaction::STATUS_PENDING) {
                $txService = $this->transactionService ?? app(TransactionService::class);
                $txService->moveToProcessing($lockedTransaction, $cashier);
            }

            return $payment;
        });
    }

    /**
     * Melunasi seluruh sisa item yang belum dibayar pada transaksi.
     */
    public function payRemaining(
        Transaction $transaction,
        string $method,
        User $cashier,
        ?int $cashReceived = null,
        ?string $payerName = null
    ): TransactionPayment {
        $remainingItems = $this->remainingItems($transaction);

        if ($remainingItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Tidak ada sisa item yang perlu dibayar.',
            ]);
        }

        $itemsPayload = $remainingItems->map(fn (TransactionDetail $detail): array => [
            'detail_id' => $detail->id,
            'qty' => $detail->remaining_quantity,
        ])->all();

        return $this->pay($transaction, $itemsPayload, $method, $cashier, $cashReceived, $payerName);
    }
}