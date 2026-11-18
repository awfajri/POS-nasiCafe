<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        $password = env('SEED_DEFAULT_PASSWORD', 'password');

        $accounts = [
            [
                'name' => 'Owner NasiCafe',
                'email' => 'owner@nasicafe.test',
                'password' => $password,
                'role' => User::ROLE_OWNER,
            ],
            [
                'name' => 'Kasir NasiCafe',
                'email' => 'kasir@nasicafe.test',
                'password' => $password,
                'role' => User::ROLE_CASHIER,
            ],
            [
                'name' => 'Pelanggan NasiCafe',
                'email' => 'pelanggan@nasicafe.test',
                'password' => $password,
                'role' => User::ROLE_CUSTOMER,
            ],
        ];

        foreach ($accounts as $account) {
            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => $account['password'],
                    'role' => $account['role'], // diset eksplisit, bukan fillable
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }

        // 3–5 pelanggan dummy (role customer).
        $customers = [
            ['name' => 'Budi Santoso', 'email' => 'budi@example.test'],
            ['name' => 'Siti Rahayu', 'email' => 'siti@example.test'],
            ['name' => 'Agus Wijaya', 'email' => 'agus@example.test'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@example.test'],
        ];

        foreach ($customers as $customer) {
            User::query()->updateOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'password' => $password,
                    'role' => User::ROLE_CUSTOMER,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
