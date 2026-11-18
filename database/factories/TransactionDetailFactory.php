<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TransactionDetail> */
class TransactionDetailFactory extends Factory
{
    protected $model = TransactionDetail::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 3);
        $price = fake()->numberBetween(8, 40) * 1000;

        return [
            'transaction_id' => Transaction::factory(),
            'menu_id' => Menu::factory(),
            'quantity' => $quantity,
            'unit_price' => $price,
            'subtotal' => $quantity * $price,
            'paid_quantity' => 0,
        ];
    }
}