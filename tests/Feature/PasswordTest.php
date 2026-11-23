<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_halaman_ubah_sandi_bisa_dibuka_semua_peran(): void
    {
        foreach (['owner', 'cashier', 'customer'] as $role) {
            $this->actingAs(User::factory()->{$role}()->create())
                ->get(route('password.edit'))
                ->assertOk();
        }
    }

    public function test_ubah_sandi_berhasil_dan_token_ingat_saya_diganti(): void
    {
        $user = User::factory()->customer()->create(['password' => 'password123']);
        $oldRememberToken = $user->remember_token;

        $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'password123',
            'password' => 'sandibaru123',
            'password_confirmation' => 'sandibaru123',
        ])->assertRedirect(route('password.edit'))->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('sandibaru123', $user->password));
        $this->assertNotSame($oldRememberToken, $user->remember_token);
    }

    public function test_sandi_lama_salah_ditolak_dan_sandi_tidak_berubah(): void
    {
        $user = User::factory()->cashier()->create(['password' => 'password123']);

        $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'salah',
            'password' => 'sandibaru123',
            'password_confirmation' => 'sandibaru123',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
    }
}