<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class TransactionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * Menghasilkan minimal 35 transaksi dummy dalam 14 hari terakhir dengan
     * alur status yang konsisten terhadap kontrak:
     *  - completed  : lunas, paid_amount = total, punya TransactionPayment.
     *  - processing : bervariasi (lunas / split bill parsial / belum bayar),
     *                 minimal 3 nota split bill dengan 2-3 pembayaran.
     *  - pending    : unpaid, paid_amount = 0 (self_order diisi expires_at).
     *  - cancelled  : unpaid, paid_amount = 0.
     *
     * Seeder TIDAK mengubah stok menu. total_amount selalu dihitung dari
     * subtotal detail, dan unit_price diambil dari harga menu asli.
     */
    public function run(): void
    {
        // Hindari duplikasi jika seeder sudah pernah dijalankan.
        if (Transaction::query()->exists()) {
            return;
        }

        $menus = Menu::query()->get();

        if ($menus->isEmpty()) {
            return;
        }

        $cashier = User::query()->where('role', User::ROLE_CASHIER)->first()
            ?? User::factory()->cashier()->create();

        $customer = User::query()->where('role', User::ROLE_CUSTOMER)->first();

        // Hari 6 dan 7 sengaja dikosongkan agar grafik harian punya titik
        // bernilai 0 (menguji zero-fill pada ReportService::dailySales).
        $dayPool = [0, 1, 2, 3, 4, 5, 8, 9, 10, 11, 12, 13];

        foreach ($this->buildSpecs() as $i => $spec) {
            $day = $dayPool[$i % count($dayPool)];

            $createdAt = now()
                ->subDays($day)
                ->setTime(fake()->numberBetween(8, 21), fake()->numberBetween(0, 59), 0);

            // Transaksi hari ini tidak boleh ber-timestamp masa depan
            // (nota tidak mungkin dibuat sebelum jamnya berlalu).
            if ($day === 0 && $createdAt->isAfter(now())) {
                $createdAt = now();
            }

            $this->createTransaction($spec, $createdAt, $menus, $cashier, $customer);
        }
    }

    /**
     * Rencana transaksi: status + cara pembayaran (full/split/none).
     *
     * @return array<int, array{status: string, payment: string, pay_count: int}>
     */
    protected function buildSpecs(): array
    {
        $specs = [];

        // completed: 15 nota lunas.
        for ($i = 0; $i < 15; $i++) {
            $specs[] = ['status' => 'completed', 'payment' => 'full', 'pay_count' => 1];
        }

        // processing lunas: 4 nota.
        for ($i = 0; $i < 4; $i++) {
            $specs[] = ['status' => 'processing_paid', 'payment' => 'full', 'pay_count' => 1];
        }

        // processing split bill: 3 nota (2-3 pembayaran) — wajib sesuai kontrak.
        $specs[] = ['status' => 'processing_split', 'payment' => 'split', 'pay_count' => 2];
        $specs[] = ['status' => 'processing_split', 'payment' => 'split', 'pay_count' => 3];
        $specs[] = ['status' => 'processing_split', 'payment' => 'split', 'pay_count' => 3];

        // processing belum bayar: 5 nota.
        for ($i = 0; $i < 5; $i++) {
            $specs[] = ['status' => 'processing_unpaid', 'payment' => 'none', 'pay_count' => 0];
        }

        // pending: 6 nota.
        for ($i = 0; $i < 6; $i++) {
            $specs[] = ['status' => 'pending', 'payment' => 'none', 'pay_count' => 0];
        }

        // cancelled: 5 nota.
        for ($i = 0; $i < 5; $i++) {
            $specs[] = ['status' => 'cancelled', 'payment' => 'none', 'pay_count' => 0];
        }

        // Acak urutan agar tidak semua transaksi serupa mengelompok di hari sama.
        shuffle($specs);

        return $specs;
    }

    /**
     * Bangun satu transaksi lengkap beserta detail dan pembayarannya.
     *
     * @param  array{status: string, payment: string, pay_count: int}  $spec
     * @param  Collection<int, Menu>  $menus
     */
    protected function createTransaction(
        array $spec,
        Carbon $createdAt,
        Collection $menus,
        User $cashier,
        ?User $customer
    ): void {
        $status = $spec['status'];
        $isFull = $spec['payment'] === 'full';
        $isSplit = $spec['payment'] === 'split';

        $source = match ($status) {
            'pending' => fake()->boolean(70)
                ? Transaction::SOURCE_SELF_ORDER
                : Transaction::SOURCE_POS,
            default => fake()->boolean(35)
                ? Transaction::SOURCE_SELF_ORDER
                : Transaction::SOURCE_POS,
        };

        // 1-4 item per nota, harga diambil dari menu asli.
        $itemCount = fake()->numberBetween(1, 4);
        $selected = $menus->random(min($itemCount, $menus->count()));

        $rows = [];
        foreach ($selected as $menu) {
            $rows[] = ['menu' => $menu, 'qty' => fake()->numberBetween(1, 3)];
        }

        // Split bill butuh cukup unit agar bisa dibagi 2-3 pembayaran
        // dan tetap menyisakan porsi belum dibayar (partial).
        if ($isSplit) {
            while ($this->totalUnits($rows) < 5) {
                $rows[0]['qty']++;
            }
        }

        $total = 0;
        foreach ($rows as $row) {
            $total += $row['menu']->price * $row['qty'];
        }

        // Rencana pembayaran -> menentukan paid_quantity per detail.
        $paymentPlan = [];
        $paidQtyPerRow = array_fill(0, count($rows), 0);

        if ($isFull) {
            $paymentPlan = [$this->allUnits($rows)];
            $paidQtyPerRow = array_map(fn (array $row): int => $row['qty'], $rows);
        } elseif ($isSplit) {
            $paymentPlan = $this->splitUnits($rows, $spec['pay_count']);
            foreach ($paymentPlan as $group) {
                foreach ($group as $rowIdx => $qty) {
                    $paidQtyPerRow[$rowIdx] += $qty;
                }
            }
        }

        $paidAmount = 0;
        foreach ($paymentPlan as $group) {
            foreach ($group as $rowIdx => $qty) {
                $paidAmount += $rows[$rowIdx]['menu']->price * $qty;
            }
        }

        $statusValue = match ($status) {
            'completed' => Transaction::STATUS_COMPLETED,
            'processing_paid', 'processing_split', 'processing_unpaid' => Transaction::STATUS_PROCESSING,
            'pending' => Transaction::STATUS_PENDING,
            'cancelled' => Transaction::STATUS_CANCELLED,
        };

        $paymentStatusValue = match ($spec['payment']) {
            'full' => Transaction::PAYMENT_PAID,
            'split' => Transaction::PAYMENT_PARTIAL,
            default => Transaction::PAYMENT_UNPAID,
        };

        $stockDeducted = in_array($statusValue, [Transaction::STATUS_PROCESSING, Transaction::STATUS_COMPLETED], true);

        // Self-order yang masih pending diisi expires_at (created_at + masa berlaku).
        $expiresAt = ($source === Transaction::SOURCE_SELF_ORDER && $statusValue === Transaction::STATUS_PENDING)
            ? $createdAt->copy()->addMinutes((int) config('nasicafe.order_expiry_minutes', 15))
            : null;

        $invoiceCode = 'INV-'.$createdAt->format('Ymd').'-F'.str_pad(
            (string) random_int(1, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        $transaction = Transaction::factory()->create([
            'invoice_code' => $invoiceCode,
            'user_id' => $source === Transaction::SOURCE_SELF_ORDER ? $customer?->id : null,
            'cashier_id' => $source === Transaction::SOURCE_POS ? $cashier->id : null,
            'customer_name' => fake()->name(),
            'table_number' => (string) fake()->numberBetween(1, 20),
            'source' => $source,
            'total_amount' => $total,
            'paid_amount' => $paidAmount,
            'status' => $statusValue,
            'payment_status' => $paymentStatusValue,
            'stock_deducted' => $stockDeducted,
            'expires_at' => $expiresAt,
        ]);

        $transaction->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

        // Detail transaksi (unit_price = harga menu, subtotal = harga * qty).
        $detailModels = [];
        foreach ($rows as $i => $row) {
            $detailModels[$i] = TransactionDetail::factory()->create([
                'transaction_id' => $transaction->id,
                'menu_id' => $row['menu']->id,
                'quantity' => $row['qty'],
                'unit_price' => $row['menu']->price,
                'subtotal' => $row['menu']->price * $row['qty'],
                'paid_quantity' => $paidQtyPerRow[$i],
            ]);

            $detailModels[$i]->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
        }

        // Pembayaran (full: satu pembayaran lunas; split: 2-3 pembayaran parsial).
        foreach ($paymentPlan as $group) {
            $paidItemsJson = [];
            $amount = 0;

            foreach ($group as $rowIdx => $qty) {
                $row = $rows[$rowIdx];
                $subtotal = $row['menu']->price * $qty;

                $paidItemsJson[] = [
                    'detail_id' => $detailModels[$rowIdx]->id,
                    'menu_name' => $row['menu']->name,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ];

                $amount += $subtotal;
            }

            $method = fake()->randomElement(['cash', 'qris']);
            $cashReceived = $method === 'cash' ? $amount + fake()->randomElement([0, 5000, 10000]) : null;
            $changeAmount = $method === 'cash' ? ($cashReceived - $amount) : 0;

            $payment = TransactionPayment::factory()->create([
                'transaction_id' => $transaction->id,
                'cashier_id' => $cashier->id,
                'payer_name' => fake()->optional(0.5)->name(),
                'payment_method' => $method,
                'amount_paid' => $amount,
                'cash_received' => $cashReceived,
                'change_amount' => $changeAmount,
                'paid_items_json' => $paidItemsJson,
            ]);

            $payment->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
        }
    }

    /**
     * Jumlah total unit (porsi) pada kumpulan baris detail.
     *
     * @param  array<int, array{menu: Menu, qty: int}>  $rows
     */
    protected function totalUnits(array $rows): int
    {
        return array_sum(array_map(fn (array $row): int => $row['qty'], $rows));
    }

    /**
     * Satu kelompok pembayaran berisi seluruh unit (dipakai pembayaran lunas).
     *
     * @param  array<int, array{menu: Menu, qty: int}>  $rows
     * @return array<int, int>  map row_idx => qty
     */
    protected function allUnits(array $rows): array
    {
        $agg = [];

        foreach ($rows as $i => $row) {
            $agg[$i] = $row['qty'];
        }

        return $agg;
    }

    /**
     * Pecah unit item menjadi beberapa kelompok pembayaran (split bill),
     * dengan minimal 1 unit tersisa belum dibayar agar statusnya partial.
     *
     * @param  array<int, array{menu: Menu, qty: int}>  $rows
     * @return array<int, array<int, int>>  list kelompok: [row_idx => qty]
     */
    protected function splitUnits(array $rows, int $payCount): array
    {
        $units = [];

        foreach ($rows as $i => $row) {
            for ($k = 0; $k < $row['qty']; $k++) {
                $units[] = $i;
            }
        }

        // Sisakan minimal 1 unit belum dibayar, tapi pastikan tiap kelompok
        // pembayaran tetap mendapat minimal 1 unit.
        $paidCount = max($payCount, count($units) - 1);
        $paidUnits = array_slice($units, 0, $paidCount);

        // Bagi rata ke $payCount kelompok, setiap kelompok minimal 1 unit.
        $base = intdiv($paidCount, $payCount);
        $remainder = $paidCount % $payCount;

        $groups = [];
        $cursor = 0;

        for ($g = 0; $g < $payCount; $g++) {
            $size = $base + ($g < $remainder ? 1 : 0);
            $groups[] = array_slice($paidUnits, $cursor, $size);
            $cursor += $size;
        }

        $result = [];

        foreach ($groups as $group) {
            $agg = [];

            foreach ($group as $rowIdx) {
                $agg[$rowIdx] = ($agg[$rowIdx] ?? 0) + 1;
            }

            $result[] = $agg;
        }

        return $result;
    }
}
