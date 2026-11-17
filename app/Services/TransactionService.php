<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    /**
     * Buat pesanan baru beserta detailnya.
     *
     * @param  array<int, array{menu_id: int|string, quantity: int|string}>  $items
     */
    public function createOrder(
        array $items,
        string $customerName,
        ?string $tableNumber,
        string $source,
        ?User $customer = null,
        ?User $cashier = null
    ): Transaction {
        $merged = [];

        foreach ($items as $item) {
            $menuId = (int) ($item['menu_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($menuId < 1 || $quantity < 1) {
                throw ValidationException::withMessages([
                    'items' => 'Item pesanan tidak valid.',
                ]);
            }

            $merged[$menuId] = ($merged[$menuId] ?? 0) + $quantity;
        }

        if ($merged === []) {
            throw ValidationException::withMessages([
                'items' => 'Pesanan minimal berisi satu item.',
            ]);
        }

        return DB::transaction(function () use ($merged, $customerName, $tableNumber, $source, $customer, $cashier): Transaction {
            $menus = Menu::query()
                ->whereIn('id', array_keys($merged))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0;
            $rows = [];

            foreach ($merged as $menuId => $quantity) {
                $menu = $menus->get($menuId);

                if (! $menu || ! $menu->is_available) {
                    throw ValidationException::withMessages([
                        'items' => 'Ada menu yang tidak tersedia atau sudah tidak dijual.',
                    ]);
                }

                if ($menu->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$menu->name} tidak cukup (sisa {$menu->stock})",
                    ]);
                }

                $subtotal = $menu->price * $quantity;
                $total += $subtotal;

                $rows[] = [
                    'menu_id' => $menu->id,
                    'quantity' => $quantity,
                    'unit_price' => $menu->price,
                    'subtotal' => $subtotal,
                    'paid_quantity' => 0,
                ];
            }

            $transaction = Transaction::create([
                'invoice_code' => $this->generateInvoiceCode(),
                'user_id' => $customer?->id,
                'cashier_id' => $cashier?->id,
                'customer_name' => $customerName,
                'table_number' => $tableNumber,
                'source' => $source,
                'total_amount' => $total,
                'paid_amount' => 0,
                'status' => Transaction::STATUS_PENDING,
                'payment_status' => Transaction::PAYMENT_UNPAID,
                'stock_deducted' => false,
                'expires_at' => $source === Transaction::SOURCE_SELF_ORDER
                    ? now()->addMinutes((int) config('nasicafe.order_expiry_minutes', 15))
                    : null,
            ]);

            $transaction->details()->createMany($rows);

            return $transaction->load('details');
        });
    }    /**
     * Status tujuan yang sah dari status saat ini.
     *
     * @return array<int, string>
     */
    public function allowedTransitions($transaction): array
    {
        return match ($transaction->status) {
            Transaction::STATUS_PENDING => [Transaction::STATUS_PROCESSING, Transaction::STATUS_CANCELLED],
            Transaction::STATUS_PROCESSING => [Transaction::STATUS_COMPLETED, Transaction::STATUS_CANCELLED],
            default => [],
        };
    }

    public function changeStatus($transaction, string $to, ?User $actor = null): Transaction
    {
        return DB::transaction(function () use ($transaction, $to, $actor): Transaction {
            $tx = Transaction::query()->lockForUpdate()->findOrFail($transaction->getKey());

            if (! in_array($to, $this->allowedTransitions($tx), true)) {
                throw ValidationException::withMessages([
                    'status' => "Status tidak dapat diubah dari {$tx->status} ke {$to}.",
                ]);
            }

            if ($to === Transaction::STATUS_PROCESSING) {
                $this->moveToProcessing($tx, $actor);

                return $tx->refresh();
            }

            if ($to === Transaction::STATUS_COMPLETED) {
                if ($tx->payment_status !== Transaction::PAYMENT_PAID) {
                    throw ValidationException::withMessages([
                        'status' => 'Transaksi baru bisa diselesaikan setelah pembayaran lunas.',
                    ]);
                }

                $tx->update([
                    'status' => Transaction::STATUS_COMPLETED,
                    'expires_at' => null,
                ]);

                return $tx;
            }

            if ((int) $tx->paid_amount !== 0) {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi yang sudah memiliki pembayaran tidak dapat dibatalkan.',
                ]);
            }

            $this->restoreStock($tx);

            $tx->update([
                'status' => Transaction::STATUS_CANCELLED,
                'expires_at' => null,
            ]);

            return $tx;
        });
    }

    public function moveToProcessing($transaction, ?User $actor = null): void
    {
        DB::transaction(function () use ($transaction, $actor): void {
            $tx = Transaction::query()->lockForUpdate()->findOrFail($transaction->getKey());

            if ($tx->status !== Transaction::STATUS_PENDING) {
                return;
            }

            $this->deductStock($tx);

            $tx->update([
                'status' => Transaction::STATUS_PROCESSING,
                'cashier_id' => $tx->cashier_id ?? $actor?->id,
                'expires_at' => null,
            ]);
        });
    }    /**
     * Potong stok untuk transaksi ini. Idempoten lewat flag stock_deducted.
     */
    public function deductStock($transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $tx = Transaction::query()->lockForUpdate()->findOrFail($transaction->getKey());

            if ($tx->stock_deducted) {
                return;
            }

            $details = $tx->details()->orderBy('id')->get();

            $menus = Menu::query()
                ->whereIn('id', $details->pluck('menu_id')->all())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($details as $detail) {
                $menu = $menus->get($detail->menu_id);

                if (! $menu || $menu->stock < $detail->quantity) {
                    $name = $menu->name ?? 'Menu';
                    $stock = $menu->stock ?? 0;

                    throw ValidationException::withMessages([
                        'items' => "Stok {$name} tidak cukup (sisa {$stock})",
                    ]);
                }
            }

            foreach ($details as $detail) {
                $menus->get($detail->menu_id)->decrement('stock', $detail->quantity);
            }

            $tx->update(['stock_deducted' => true]);
        });
    }

    /**
     * Kembalikan stok yang sebelumnya dipotong.
     */
    public function restoreStock($transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $tx = Transaction::query()->lockForUpdate()->findOrFail($transaction->getKey());

            if (! $tx->stock_deducted) {
                return;
            }

            $details = $tx->details()->orderBy('id')->get();

            $menus = Menu::query()
                ->whereIn('id', $details->pluck('menu_id')->all())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($details as $detail) {
                $menus->get($detail->menu_id)?->increment('stock', $detail->quantity);
            }

            $tx->update(['stock_deducted' => false]);
        });
    }

    /**
     * Batalkan semua self order yang sudah kedaluwarsa.
     *
     * @return int jumlah transaksi yang dibatalkan
     */
    public function cancelExpired(): int
    {
        $candidates = Transaction::query()
            ->where('source', Transaction::SOURCE_SELF_ORDER)
            ->where('status', Transaction::STATUS_PENDING)
            ->where('paid_amount', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->pluck('id');

        $cancelled = 0;

        foreach ($candidates as $id) {
            try {
                $tx = Transaction::query()->findOrFail($id);
                $this->changeStatus($tx, Transaction::STATUS_CANCELLED);
                $cancelled++;
            } catch (ValidationException) {
                continue;
            }
        }

        return $cancelled;
    }

    /**
     * Kode nota harian: INV-YYYYMMDD-NNNN (urut per hari).
     */
    protected function generateInvoiceCode(): string
    {
        $prefix = 'INV-'.now()->format('Ymd').'-';

        $last = Transaction::query()
            ->where('invoice_code', 'like', $prefix.'%')
            ->where('invoice_code', 'not like', $prefix.'F%')
            ->orderByDesc('invoice_code')
            ->lockForUpdate()
            ->value('invoice_code');

        $sequence = $last === null ? 1 : ((int) substr($last, strlen($prefix))) + 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}