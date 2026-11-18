<?php

namespace App\Models;

use Database\Factories\TransactionDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['transaction_id', 'menu_id', 'quantity', 'unit_price', 'subtotal', 'paid_quantity'])]
class TransactionDetail extends Model
{
    /** @use HasFactory<TransactionDetailFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'subtotal' => 'integer',
            'paid_quantity' => 'integer',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /** Sisa porsi yang belum dibayar (dipakai split bill). */
    protected function remainingQuantity(): Attribute
    {
        return Attribute::get(fn (): int => $this->quantity - $this->paid_quantity);
    }
}