<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $service;

    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TransactionService::class);
        $this->cashier = User::factory()->cashier()->create();
    }

    private function makeMenu(int $stock = 10, int $price = 25000): Menu
    {
        return Menu::factory()->create([
            'stock' => $stock,
            'price' => $price,
            'is_available' => true,
        ]);
    }

    private function makeOrder(Menu $menu, int $qty = 2): Transaction
    {
        return $this->service->createOrder(
            [['menu_id' => $menu->id, 'quantity' => $qty]],
            'Pelanggan Uji',
            '1',
            Transaction::SOURCE_POS,
            null,
            $this->cashier,
        );
    }

    public function test_pending_ke_processing_memotong_stok(): void
    {
        $menu = $this->makeMenu(10);
        $transaction = $this->makeOrder($menu, 3);

        $this->assertSame(10, $menu->fresh()->stock);

        $this->service->changeStatus($transaction, Transaction::STATUS_PROCESSING, $this->cashier);

        $this->assertSame(7, $menu->fresh()->stock);
        $this->assertTrue($transaction->fresh()->stock_deducted);
        $this->assertSame(Transaction::STATUS_PROCESSING, $transaction->fresh()->status);
    }

    public function test_pending_ke_cancelled_tidak_mengubah_stok(): void
    {
        $menu = $this->makeMenu(10);
        $transaction = $this->makeOrder($menu, 3);

        $this->service->changeStatus($transaction, Transaction::STATUS_CANCELLED, $this->cashier);

        $this->assertSame(10, $menu->fresh()->stock);
        $this->assertFalse($transaction->fresh()->stock_deducted);
        $this->assertSame(Transaction::STATUS_CANCELLED, $transaction->fresh()->status);
    }

    public function test_processing_ke_completed_wajib_lunas(): void
    {
        $menu = $this->makeMenu(10);
        $transaction = $this->makeOrder($menu, 2);
        $this->service->changeStatus($transaction, Transaction::STATUS_PROCESSING, $this->cashier);

        try {
            $this->service->changeStatus($transaction->fresh(), Transaction::STATUS_COMPLETED, $this->cashier);
            $this->fail('Seharusnya ditolak karena belum lunas.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }

        $transaction->fresh()->update([
            'paid_amount' => $transaction->fresh()->total_amount,
            'payment_status' => Transaction::PAYMENT_PAID,
        ]);

        $this->service->changeStatus($transaction->fresh(), Transaction::STATUS_COMPLETED, $this->cashier);

        $this->assertSame(Transaction::STATUS_COMPLETED, $transaction->fresh()->status);
    }

    public function test_processing_ke_cancelled_mengembalikan_stok(): void
    {
        $menu = $this->makeMenu(10);
        $transaction = $this->makeOrder($menu, 4);
        $this->service->changeStatus($transaction, Transaction::STATUS_PROCESSING, $this->cashier);

        $this->assertSame(6, $menu->fresh()->stock);

        $this->service->changeStatus($transaction->fresh(), Transaction::STATUS_CANCELLED, $this->cashier);

        $this->assertSame(10, $menu->fresh()->stock);
        $this->assertFalse($transaction->fresh()->stock_deducted);
    }

    public function test_pending_ke_completed_langsung_ditolak(): void
    {
        $menu = $this->makeMenu(10);
        $transaction = $this->makeOrder($menu, 1);

        $this->expectException(ValidationException::class);

        $this->service->changeStatus($transaction, Transaction::STATUS_COMPLETED, $this->cashier);
    }

    public function test_completed_bersifat_final(): void
    {
        $transaction = Transaction::factory()->completed()->create([
            'total_amount' => 50000,
            'paid_amount' => 50000,
        ]);

        $this->expectException(ValidationException::class);

        $this->service->changeStatus($transaction, Transaction::STATUS_PROCESSING, $this->cashier);
    }

    public function test_stok_hanya_terpotong_sekali_walau_dipanggil_dua_kali(): void
    {
        $menu = $this->makeMenu(10);
        $transaction = $this->makeOrder($menu, 3);

        $this->service->deductStock($transaction);
        $this->assertSame(7, $menu->fresh()->stock);

        $this->service->deductStock($transaction->fresh());
        $this->assertSame(7, $menu->fresh()->stock);
    }

    public function test_cancel_expired_hanya_membatalkan_self_order_kedaluwarsa(): void
    {
        $menu = $this->makeMenu(10);

        $expired = $this->service->createOrder(
            [['menu_id' => $menu->id, 'quantity' => 1]],
            'Pelanggan Lama',
            '2',
            Transaction::SOURCE_SELF_ORDER,
            User::factory()->customer()->create(),
        );
        $expired->forceFill(['expires_at' => now()->subMinutes(30)])->save();

        $stillValid = $this->service->createOrder(
            [['menu_id' => $menu->id, 'quantity' => 1]],
            'Pelanggan Baru',
            '3',
            Transaction::SOURCE_SELF_ORDER,
            User::factory()->customer()->create(),
        );

        $posTransaction = $this->makeOrder($menu, 1);
        $posTransaction->forceFill(['expires_at' => now()->subMinutes(30)])->save();

        $cancelled = $this->service->cancelExpired();

        $this->assertSame(1, $cancelled);
        $this->assertSame(Transaction::STATUS_CANCELLED, $expired->fresh()->status);
        $this->assertSame(Transaction::STATUS_PENDING, $stillValid->fresh()->status);
        $this->assertSame(Transaction::STATUS_PENDING, $posTransaction->fresh()->status);
    }
}