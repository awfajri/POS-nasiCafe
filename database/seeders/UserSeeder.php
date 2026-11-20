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
            $user = User::query()->firstOrNew([
                    'email' => $account['email']
                ]);
            $user->name = $account['name'];
            $user->password = $account['password'];
            $user->role = $account['role'];
            $user->is_active = true;
            $user->email_verified_at = now();

            $user->save();
        }

        // 3–5 pelanggan dummy (role customer).
        $customers = [
            ['name' => 'Budi Santoso', 'email' => 'budi@example.test'],
            ['name' => 'Siti Rahayu', 'email' => 'siti@example.test'],
            ['name' => 'Agus Wijaya', 'email' => 'agus@example.test'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@example.test'],
        ];

        foreach ($customers as $customer) {
            $user = User::query()->firstOrNew([
                'email' => $customer['email']
            ]);
            $user->name = $customer['name'];
            $user->password = $password;
            $user->role = User::ROLE_CUSTOMER;
            $user->is_active = true;
            $user->email_verified_at = now();

            $user->save();
        }
    }
}
