<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->owner = User::factory()->owner()->create();
    }

    public function test_index_menampilkan_daftar_dengan_pencarian_dan_filter_peran(): void
    {
        User::factory()->cashier()->create(['name' => 'Budi Kasir', 'email' => 'budi@nasicafe.test']);
        User::factory()->customer()->create(['name' => 'Siti Pelanggan', 'email' => 'siti@nasicafe.test']);

        $this->actingAs($this->owner)->get(route('owner.users.index'))
            ->assertOk()->assertSee('Budi Kasir')->assertSee('Siti Pelanggan');

        $this->actingAs($this->owner)->get(route('owner.users.index', ['search' => 'budi']))
            ->assertOk()->assertSee('Budi Kasir')->assertDontSee('Siti Pelanggan');

        $this->actingAs($this->owner)->get(route('owner.users.index', ['role' => 'customer']))
            ->assertOk()->assertSee('Siti Pelanggan')->assertDontSee('Budi Kasir');
    }

    public function test_index_mengabaikan_parameter_filter_yang_tidak_valid(): void
    {
        $this->actingAs($this->owner)
            ->get(route('owner.users.index', ['role' => 'admin', 'search' => ['x']]))
            ->assertOk();
    }

    public function test_halaman_tambah_dan_ubah_bisa_dibuka(): void
    {
        $user = User::factory()->cashier()->create();

        $this->actingAs($this->owner)->get(route('owner.users.create'))->assertOk();
        $this->actingAs($this->owner)->get(route('owner.users.edit', $user))->assertOk();
        $this->actingAs($this->owner)->get(route('owner.users.edit', $this->owner))->assertOk();
    }

    public function test_store_menyetel_role_eksplisit_dan_mengabaikan_is_active_dari_request(): void
    {
        $this->actingAs($this->owner)->post(route('owner.users.store'), [
            'name' => 'Kasir Baru',
            'email' => 'Kasir.Baru@NasiCafe.test',
            'password' => 'password123',
            'role' => 'cashier',
            'is_active' => 0,
        ])->assertRedirect(route('owner.users.index'));

        $user = User::where('email', 'kasir.baru@nasicafe.test')->firstOrFail();

        $this->assertSame('cashier', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNotSame('password123', $user->password);
    }

    public function test_update_tanpa_password_tidak_mengubah_password(): void
    {
        $user = User::factory()->cashier()->create(['password' => 'password123']);
        $oldHash = $user->password;

        $this->actingAs($this->owner)->put(route('owner.users.update', $user), [
            'name' => 'Nama Baru',
            'email' => $user->email,
            'role' => 'customer',
            'password' => '',
        ])->assertRedirect(route('owner.users.index'));

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('customer', $user->role);
        $this->assertSame($oldHash, $user->password);
    }

    public function test_update_dengan_password_mengganti_password(): void
    {
        $user = User::factory()->cashier()->create(['password' => 'password123']);

        $this->actingAs($this->owner)->put(route('owner.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'cashier',
            'password' => 'sandibaru123',
        ])->assertRedirect(route('owner.users.index'));

        $this->assertTrue(Hash::check('sandibaru123', $user->fresh()->password));
    }

    public function test_owner_tidak_bisa_mengubah_peran_sendiri(): void
    {
        $this->actingAs($this->owner)->put(route('owner.users.update', $this->owner), [
            'name' => $this->owner->name,
            'email' => $this->owner->email,
            'role' => 'customer',
        ])->assertSessionHasErrors('role');

        $this->assertSame('owner', $this->owner->fresh()->role);
    }

    public function test_owner_boleh_mengubah_data_sendiri_selama_peran_tetap(): void
    {
        $this->actingAs($this->owner)->put(route('owner.users.update', $this->owner), [
            'name' => 'Owner Ganti Nama',
            'email' => $this->owner->email,
            'role' => 'owner',
        ])->assertRedirect(route('owner.users.index'));

        $this->assertSame('Owner Ganti Nama', $this->owner->fresh()->name);
    }

    public function test_toggle_membalik_status_dan_akun_nonaktif_langsung_dikeluarkan(): void
    {
        $cashier = User::factory()->cashier()->create();

        $this->actingAs($this->owner)->patch(route('owner.users.toggle', $cashier))->assertRedirect();
        $this->assertFalse($cashier->fresh()->is_active);

        // Akun yang baru dinonaktifkan ditolak di request berikutnya.
        $this->actingAs($cashier->fresh())->get(route('password.edit'))->assertRedirect(route('login'));

        $this->actingAs($this->owner)->patch(route('owner.users.toggle', $cashier))->assertRedirect();
        $this->assertTrue($cashier->fresh()->is_active);
    }

    public function test_owner_tidak_bisa_menonaktifkan_diri_sendiri(): void
    {
        $this->actingAs($this->owner)->patch(route('owner.users.toggle', $this->owner))
            ->assertSessionHasErrors('user');

        $this->assertTrue($this->owner->fresh()->is_active);
    }
}