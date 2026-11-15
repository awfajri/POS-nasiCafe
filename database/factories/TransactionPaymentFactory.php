<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\TransactionPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionPayment>
 */
class TransactionPaymentFactory extends Factory
{
    protected $model = TransactionPayment::class;

    public function definition(): array
    {
        $method = fake()->randomElement(['cash', 'qris']);
        $amountPaid = fake()->numberBetween(15000, 150000);
        $cashReceived = $method === 'cash' ? $amountPaid + 5000 : null;
        $changeAmount = $method === 'cash' ? 5000 : 0;

        return [
            'transaction_id' => Transaction::factory(),
            'cashier_id' => User::factory(),
            'payer_name' => fake()->optional()->name(),
            'payment_method' => $method,
            'amount_paid' => $amountPaid,
            'cash_received' => $cashReceived,
            'change_amount' => $changeAmount,
            'paid_items_json' => [
                [
                    'transaction_item_id' => 1,
                    'qty' => 1,
                    'subtotal' => $amountPaid,
                ],
            ],
        ];
    }
}