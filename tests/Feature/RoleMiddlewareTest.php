<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // Rute sementara khusus test (tidak ada di aplikasi asli).
        if (! Route::has('login')) {
            Route::get('/_test/login', fn () => 'login')->name('login');
        }
        Route::middleware(['web', 'role:owner'])->get('/_test/owner-only', fn () => 'ok');
        Route::middleware(['web', 'role:owner,cashier'])->get('/_test/staff-only', fn () => 'ok');

        app('router')->getRoutes()->refreshNameLookups();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/_test/owner-only')->assertRedirect(route('login'));
    }

    public function test_akun_nonaktif_dikeluarkan_dengan_pesan(): void
    {
        $user = User::factory()->owner()->inactive()->create();

        $this->actingAs($user)
            ->get('/_test/owner-only')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Akun Anda telah dinonaktifkan');

        $this->assertGuest();
    }

    public function test_peran_yang_salah_mendapat_403(): void
    {
        $this->actingAs(User::factory()->cashier()->create())
            ->get('/_test/owner-only')
            ->assertForbidden();
    }

    public function test_peran_yang_benar_boleh_masuk(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->get('/_test/owner-only')
            ->assertOk();
    }

    public function test_beberapa_peran_dalam_satu_rute(): void
    {
        $this->actingAs(User::factory()->cashier()->create())
            ->get('/_test/staff-only')
            ->assertOk();

        $this->actingAs(User::factory()->customer()->create())
            ->get('/_test/staff-only')
            ->assertForbidden();
    }
}