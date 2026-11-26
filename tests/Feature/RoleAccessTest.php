<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Nama route => peran yang boleh mengakses.
     * Sumber: tabel "Hak Akses per Peran" di kontrak. Selain tabel = 403.
     */
    private const MATRIX = [
        'owner.dashboard' => ['owner'],
        'cashier.dashboard' => ['cashier'],
        'customer.dashboard' => ['customer'],
        'owner.users.index' => ['owner'],
        'owner.users.create' => ['owner'],
        'owner.categories.index' => ['owner'],
        'owner.menus.index' => ['owner'],
        'transactions.index' => ['owner', 'cashier'],
        'pos.index' => ['cashier'],
        'customer.order' => ['customer'],
        'password.edit' => ['owner', 'cashier', 'customer'],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_matriks_akses_per_peran(): void
    {
        $users = [
            'owner' => User::factory()->owner()->create(),
            'cashier' => User::factory()->cashier()->create(),
            'customer' => User::factory()->customer()->create(),
        ];

        foreach (self::MATRIX as $name => $allowed) {
            if (! Route::has($name)) {
                continue;
            }

            $url = route($name);

            // Tamu selalu diarahkan ke login.
            Auth::logout();
            $this->get($url)->assertRedirect(route('login'));

            foreach ($users as $role => $user) {
                $status = $this->actingAs($user)->get($url)->getStatusCode();

                if (in_array($role, $allowed, true)) {
                    // Yang diuji hanya otorisasi: halaman boleh saja error karena
                    // kerjaan teman belum selesai, asal bukan 403.
                    $this->assertNotSame(403, $status, "{$role} seharusnya boleh membuka {$name}");
                } else {
                    $this->assertSame(403, $status, "{$role} seharusnya mendapat 403 di {$name}");
                }
            }
        }
    }

    public function test_akun_nonaktif_ditolak_dan_langsung_keluar(): void
    {
        foreach (['owner.users.index', 'password.edit'] as $name) {
            $nonaktif = User::factory()->owner()->inactive()->create();

            $this->actingAs($nonaktif)->get(route($name))->assertRedirect(route('login'));
            $this->assertGuest();
        }
    }
}