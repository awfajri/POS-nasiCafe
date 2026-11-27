<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;
    private TransactionService $transactionService;
    private User $cashier;
    private Menu $menuNasi;
    private Menu $menuMinum;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentService = app(PaymentService::class);
        $this->transactionService = app(TransactionService::class);
        $this->cashier = User::factory()->cashier()->create();

        $category = Category::create([
            'name' => 'Paket Utama',
            'slug' => 'paket-utama',
        ]);

        $this->menuNasi = Menu::create([
            'category_id' => $category->id,
            'name' => 'Nasi Goreng Spesial',
            'price' => 20000,
            'stock' => 10,
            'min_stock' => 2,
            'is_available' => true,
        ]);

        $this->menuMinum = Menu::create([
            'category_id' => $category->id,
            'name' => 'Es Teh Manis',
            'price' => 5000,
            'stock' => 20,
            'min_stock' => 5,
            'is_available' => true,
        ]);
    }

    public function test_full_payment_marks_transaction_paid_and_moves_pending_to_processing(): void
    {
        $transaction = $this->transactionService->createOrder(
            [
                ['menu_id' => $this->menuNasi->id, 'quantity' => 2],
                ['menu_id' => $this->menuMinum->id, 'quantity' => 1],
            ],
            'Budi',
            '01',
            Transaction::SOURCE_POS,
            null,
            $this->cashier
        );

        $payment = $this->paymentService->payRemaining(
            $transaction,
            'cash',
            $this->cashier,
            50000,
            'Budi'
        );

        $transaction->refresh();

        $this->assertSame(45000, $payment->amount_paid);
        $this->assertSame(50000, $payment->cash_received);
        $this->assertSame(5000, $payment->change_amount);
        $this->assertSame(Transaction::PAYMENT_PAID, $transaction->payment_status);
        $this->assertSame(Transaction::STATUS_PROCESSING, $transaction->status);
        $this->assertSame(45000, $transaction->paid_amount);
        $this->assertTrue($transaction->stock_deducted);
        $this->assertSame(8, $this->menuNasi->fresh()->stock);
        $this->assertSame(19, $this->menuMinum->fresh()->stock);
    }

    public function test_split_bill_with_three_payers_keeps_invoice_total_intact(): void
    {
        $transaction = $this->transactionService->createOrder(
            [
                ['menu_id' => $this->menuNasi->id, 'quantity' => 2],
                ['menu_id' => $this->menuMinum->id, 'quantity' => 2],
            ],
            'Rombongan Meja 4',
            '04',
            Transaction::SOURCE_POS,
            null,
            $this->cashier
        );

        $detailNasi = $transaction->details()->where('menu_id', $this->menuNasi->id)->firstOrFail();
        $detailMinum = $transaction->details()->where('menu_id', $this->menuMinum->id)->firstOrFail();

        // Pembayar 1: 1 Nasi Goreng + 1 Es Teh (25.000)
        $p1 = $this->paymentService->pay(
            $transaction,
            [
                ['detail_id' => $detailNasi->id, 'qty' => 1],
                ['detail_id' => $detailMinum->id, 'qty' => 1],
            ],
            'cash',
            $this->cashier,
            30000,
            'Andi'
        );

        $transaction->refresh();
        $this->assertSame(5000, $p1->change_amount);
        $this->assertSame(Transaction::PAYMENT_PARTIAL, $transaction->payment_status);
        $this->assertSame(50000, $transaction->total_amount);

        // Pembayar 2: 1 Nasi Goreng via QRIS (20.000)
        $p2 = $this->paymentService->pay(
            $transaction,
            [
                ['detail_id' => $detailNasi->id, 'qty' => 1],
            ],
            'qris',
            $this->cashier,
            null,
            'Rina'
        );

        $transaction->refresh();
        $this->assertNull($p2->cash_received);
        $this->assertSame(0, $p2->change_amount);
        $this->assertSame(Transaction::PAYMENT_PARTIAL, $transaction->payment_status);
        $this->assertSame(45000, $transaction->paid_amount);

        // Pembayar 3: Sisa 1 Es Teh via Tunai pas (5.000)
        $this->paymentService->pay(
            $transaction,
            [
                ['detail_id' => $detailMinum->id, 'qty' => 1],
            ],
            'cash',
            $this->cashier,
            5000,
            'Dedi'
        );

        $transaction->refresh();
        $this->assertSame(Transaction::PAYMENT_PAID, $transaction->payment_status);
        $this->assertSame(50000, $transaction->total_amount);
        $this->assertSame(50000, $transaction->paid_amount);
        $this->assertCount(3, $transaction->payments);
        $this->assertTrue($this->paymentService->remainingItems($transaction)->isEmpty());
    }

    public function test_self_order_qris_confirmation_sets_cashier_and_clears_expires_at(): void
    {
        $customer = User::factory()->customer()->create();

        $transaction = $this->transactionService->createOrder(
            [
                ['menu_id' => $this->menuNasi->id, 'quantity' => 1],
            ],
            $customer->name,
            '07',
            Transaction::SOURCE_SELF_ORDER,
            $customer
        );

        $this->assertNotNull($transaction->expires_at);
        $this->assertNull($transaction->cashier_id);

        $this->paymentService->payRemaining(
            $transaction,
            'qris',
            $this->cashier,
            null,
            $customer->name
        );

        $transaction->refresh();
        $this->assertSame($this->cashier->id, $transaction->cashier_id);
        $this->assertNull($transaction->expires_at);
        $this->assertSame(Transaction::PAYMENT_PAID, $transaction->payment_status);
        $this->assertSame(Transaction::STATUS_PROCESSING, $transaction->status);
    }

    public function test_rejects_quantity_exceeding_remaining_unpaid_quantity(): void
    {
        $transaction = $this->transactionService->createOrder(
            [
                ['menu_id' => $this->menuNasi->id, 'quantity' => 1],
            ],
            'Tono',
            '02',
            Transaction::SOURCE_POS,
            null,
            $this->cashier
        );

        $detail = $transaction->details()->firstOrFail();

        $this->expectException(ValidationException::class);

        $this->paymentService->pay(
            $transaction,
            [
                ['detail_id' => $detail->id, 'qty' => 2],
            ],
            'cash',
            $this->cashier,
            50000
        );
    }

    public function test_rejects_detail_id_belonging_to_another_transaction(): void
    {
        $transactionA = $this->transactionService->createOrder(
            [
                ['menu_id' => $this->menuNasi->id, 'quantity' => 1],
            ],
            'Pelanggan A',
            '01',
            Transaction::SOURCE_POS,
            null,
            $this->cashier
        );

        $transactionB = $this->transactionService->createOrder(
            [
                ['menu_id' => $this->menuMinum->id, 'quantity' => 1],
            ],
            'Pelanggan B',
            '02',
            Transaction::SOURCE_POS,
            null,
            $this->cashier
        );

        $foreignDetail = $transactionB->details()->firstOrFail();

        $this->expectException(ValidationException::class);

        $this->paymentService->pay(
            $transactionA,
            [
                ['detail_id' => $foreignDetail->id, 'qty' => 1],
            ],
            'cash',
            $this->cashier,
            20000
        );
    }
}