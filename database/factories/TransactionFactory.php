<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Transaction> */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Penomoran factory memakai nomor acak unik agar tidak bertabrakan
     * dengan kode nota asli yang dibuat TransactionService (INV-YYYYMMDD-NNNN).
     */
    protected static int $invoiceSequence = 0;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 3);
        $price = fake()->numberBetween(8, 40) * 1000;

        return [
            'invoice_code' => self::nextInvoiceCode(),
            'user_id' => null,
            'cashier_id' => User::factory()->cashier(),
            'customer_name' => fake()->name(),
            'table_number' => (string) fake()->numberBetween(1, 20),
            'source' => Transaction::SOURCE_POS,
            'total_amount' => $quantity * $price,
            'paid_amount' => 0,
            'status' => Transaction::STATUS_PENDING,
            'payment_status' => Transaction::PAYMENT_UNPAID,
            'stock_deducted' => false,
            'expires_at' => null,
        ];
    }

    protected static function nextInvoiceCode(): string
    {
        static::$invoiceSequence++;

        // Awalan 6 digit + huruf F tidak mungkin bertabrakan dengan
        // penomoran resmi 4 digit milik TransactionService (INV-YYYYMMDD-NNNN).
        $suffix = 'F'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);

        return 'INV-'.now()->format('Ymd').'-'.$suffix;
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => Transaction::STATUS_PENDING,
            'paid_amount' => 0,
            'payment_status' => Transaction::PAYMENT_UNPAID,
            'stock_deducted' => false,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (): array => [
            'status' => Transaction::STATUS_PROCESSING,
            'stock_deducted' => true,
            'expires_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Transaction::STATUS_COMPLETED,
            'paid_amount' => $attributes['total_amount'] ?? 0,
            'payment_status' => Transaction::PAYMENT_PAID,
            'stock_deducted' => true,
            'expires_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => Transaction::STATUS_CANCELLED,
            'paid_amount' => 0,
            'payment_status' => Transaction::PAYMENT_UNPAID,
            'stock_deducted' => false,
            'expires_at' => null,
        ]);
    }

    public function selfOrder(): static
    {
        return $this->state(fn (): array => [
            'source' => Transaction::SOURCE_SELF_ORDER,
            'cashier_id' => null,
            'expires_at' => now()->addMinutes((int) config('nasicafe.order_expiry_minutes', 15)),
        ]);
    }
}