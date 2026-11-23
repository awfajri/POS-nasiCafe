<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_from_cashier_dashboard(): void
    {
        $this->get(route('cashier.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_cashier_can_open_cashier_dashboard(): void
    {
        $this->actingAs(User::factory()->cashier()->create())
            ->get(route('cashier.dashboard'))
            ->assertOk()
            ->assertViewIs('cashier.dashboard')
            ->assertViewHasAll(['pendingCount', 'processingCount', 'completedToday', 'totalToday', 'activeQueue', 'lowStockMenus']);
    }

    public function test_non_cashier_cannot_open_cashier_dashboard(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->get(route('cashier.dashboard'))
            ->assertForbidden();

        $this->actingAs(User::factory()->customer()->create())
            ->get(route('cashier.dashboard'))
            ->assertForbidden();
    }
}