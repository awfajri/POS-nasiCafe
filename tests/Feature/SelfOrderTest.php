<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SelfOrderTest extends TestCase
{
    use RefreshDatabase;

    private function expectedRedirectUrl(): string
    {
        return Route::has('customer.dashboard')
            ? route('customer.dashboard')
            : '/customer/dashboard';
    }

    public function test_guest_and_non_customer_cannot_access_self_order_routes(): void
    {
        $menu = Menu::factory()->create([
            'price' => 20000,
            'stock' => 10,
            'is_available' => true,
        ]);

        $payload = [
            'table_number' => 'A1',
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 1],
            ],
        ];

        $this->get(route('customer.order'))
            ->assertRedirect(route('login'));

        $this->post(route('customer.order.store'), $payload)
            ->assertRedirect(route('login'));

        $cashier = User::factory()->cashier()->create();
        $owner = User::factory()->owner()->create();

        $this->actingAs($cashier)
            ->post(route('customer.order.store'), $payload)
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('customer.order.store'), $payload)
            ->assertForbidden();

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_customer_can_place_self_order_successfully(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Budi Santoso',
        ]);

        $category = Category::factory()->create([
            'name' => 'Makanan Utama',
        ]);

        $nasiGoreng = Menu::factory()->create([
            'category_id' => $category->id,
            'name' => 'Nasi Goreng Spesial',
            'price' => 25000,
            'stock' => 10,
            'is_available' => true,
        ]);

        $esTeh = Menu::factory()->create([
            'category_id' => $category->id,
            'name' => 'Es Teh Manis',
            'price' => 8000,
            'stock' => 15,
            'is_available' => true,
        ]);

        $response = $this->actingAs($customer)->post(route('customer.order.store'), [
            'table_number' => 'A5',
            'items' => [
                ['menu_id' => $nasiGoreng->id, 'quantity' => 2],
                ['menu_id' => $esTeh->id, 'quantity' => 3],
            ],
        ]);

        $response->assertRedirect($this->expectedRedirectUrl());
        $response->assertSessionHas('success');

        $expectedTotal = (25000 * 2) + (8000 * 3);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $customer->id,
            'customer_name' => 'Budi Santoso',
            'table_number' => 'A5',
            'source' => Transaction::SOURCE_SELF_ORDER,
            'total_amount' => $expectedTotal,
            'paid_amount' => 0,
            'status' => Transaction::STATUS_PENDING,
            'payment_status' => Transaction::PAYMENT_UNPAID,
            'stock_deducted' => false,
        ]);

        $transaction = Transaction::query()->firstOrFail();

        $this->assertNotNull($transaction->expires_at);

        $this->assertDatabaseHas('transaction_details', [
            'transaction_id' => $transaction->id,
            'menu_id' => $nasiGoreng->id,
            'quantity' => 2,
            'unit_price' => 25000,
            'subtotal' => 50000,
            'paid_quantity' => 0,
        ]);

        $this->assertDatabaseHas('transaction_details', [
            'transaction_id' => $transaction->id,
            'menu_id' => $esTeh->id,
            'quantity' => 3,
            'unit_price' => 8000,
            'subtotal' => 24000,
            'paid_quantity' => 0,
        ]);

        // Stok tetap terjaga saat transaksi masih berstatus pending (baru terpotong saat diproses)
        $this->assertSame(10, $nasiGoreng->fresh()->stock);
        $this->assertSame(15, $esTeh->fresh()->stock);
    }

    public function test_self_order_is_rejected_when_stock_is_insufficient(): void
    {
        $customer = User::factory()->customer()->create();

        $menu = Menu::factory()->create([
            'name' => 'Ayam Geprek',
            'price' => 22000,
            'stock' => 3,
            'is_available' => true,
        ]);

        $response = $this->actingAs($customer)
            ->from(route('customer.order'))
            ->post(route('customer.order.store'), [
                'table_number' => 'B2',
                'items' => [
                    ['menu_id' => $menu->id, 'quantity' => 5],
                ],
            ]);

        $response->assertRedirect(route('customer.order'));
        $response->assertSessionHasErrors('items');

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseCount('transaction_details', 0);
        $this->assertSame(3, $menu->fresh()->stock);
    }

    public function test_self_order_ignores_tampered_price_subtotal_and_total_from_request(): void
    {
        $customer = User::factory()->customer()->create();

        $menu = Menu::factory()->create([
            'name' => 'Kopi Susu Gula Aren',
            'price' => 18000,
            'stock' => 20,
            'is_available' => true,
        ]);

        $response = $this->actingAs($customer)->post(route('customer.order.store'), [
            'table_number' => 'C3',
            'total' => 500,
            'total_amount' => 500,
            'items' => [
                [
                    'menu_id' => $menu->id,
                    'quantity' => 2,
                    'price' => 250,
                    'unit_price' => 250,
                    'subtotal' => 500,
                ],
            ],
        ]);

        $response->assertRedirect($this->expectedRedirectUrl());
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $customer->id,
            'source' => Transaction::SOURCE_SELF_ORDER,
            'total_amount' => 36000,
        ]);

        $transaction = Transaction::query()->firstOrFail();

        $this->assertDatabaseHas('transaction_details', [
            'transaction_id' => $transaction->id,
            'menu_id' => $menu->id,
            'quantity' => 2,
            'unit_price' => 18000,
            'subtotal' => 36000,
        ]);
    }
}