<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityQaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_role_dan_is_active_tidak_bisa_diisi_massal(): void
    {
        try {
            $user = User::create([
                'name' => 'Penyusup',
                'email' => 'penyusup@nasicafe.test',
                'password' => 'password123',
                'role' => 'owner',
                'is_active' => false,
            ]);
        } catch (MassAssignmentException) {
            // Kalau aplikasi dikonfigurasi melempar error, itu juga aman.
            $this->assertTrue(true);

            return;
        }

        $user->refresh();
        $this->assertSame('customer', $user->role);
        $this->assertTrue($user->is_active);
    }

    public function test_register_dengan_role_owner_tetap_menjadi_customer(): void
    {
        $this->post('/register', [
            'name' => 'Pendaftar Nakal',
            'email' => 'nakal@nasicafe.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'owner',
        ]);

        $this->assertSame('customer', User::where('email', 'nakal@nasicafe.test')->value('role'));
    }

    public function test_nama_pengguna_di_daftar_di_escape(): void
    {
        $payload = '<script>alert(1)</script>';
        User::factory()->customer()->create(['name' => $payload]);

        $this->actingAs(User::factory()->owner()->create())
            ->get(route('owner.users.index'))
            ->assertOk()
            ->assertDontSee($payload, false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_pelanggan_a_tidak_bisa_melihat_pesanan_pelanggan_b(): void
    {
        if (! class_exists(Transaction::class) || ! method_exists(Transaction::class, 'factory')) {
            $this->markTestSkipped('Model atau factory Transaction belum tersedia di develop.');
        }

        // Dashboard memanggil cancelExpired(), jadi diisolasi dari implementasi Gading.
        $this->mock(TransactionService::class, function ($mock) {
            $mock->shouldReceive('cancelExpired')->andReturn(0);
        });

        $a = User::factory()->customer()->create();
        $b = User::factory()->customer()->create();

        Transaction::factory()->pending()->create(['user_id' => $a->id, 'invoice_code' => 'INV-TEST-A001']);
        Transaction::factory()->pending()->create(['user_id' => $b->id, 'invoice_code' => 'INV-TEST-B001']);

        $this->actingAs($a)->get(route('customer.dashboard'))
            ->assertOk()->assertSee('INV-TEST-A001')->assertDontSee('INV-TEST-B001');

        $this->actingAs($b)->get(route('customer.dashboard'))
            ->assertOk()->assertSee('INV-TEST-B001')->assertDontSee('INV-TEST-A001');
    }
}