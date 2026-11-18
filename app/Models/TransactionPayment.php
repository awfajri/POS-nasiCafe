<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'cashier_id',
        'payer_name',
        'payment_method',
        'amount_paid',
        'cash_received',
        'change_amount',
        'paid_items_json',
    ];

    protected $casts = [
        'amount_paid' => 'integer',
        'cash_received' => 'integer',
        'change_amount' => 'integer',
        'paid_items_json' => 'array',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}