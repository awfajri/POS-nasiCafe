<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_code',
    'user_id',
    'cashier_id',
    'customer_name',
    'table_number',
    'source',
    'total_amount',
    'paid_amount',
    'status',
    'payment_status',
    'stock_deducted',
    'expires_at',
])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    public const SOURCE_POS = 'pos';

    public const SOURCE_SELF_ORDER = 'self_order';

    public const SOURCES = [self::SOURCE_POS, self::SOURCE_SELF_ORDER];

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PARTIAL = 'partial';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_STATUSES = [self::PAYMENT_UNPAID, self::PAYMENT_PARTIAL, self::PAYMENT_PAID];

    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'paid_amount' => 'integer',
            'stock_deducted' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Kasir yang membuat transaksi. */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TransactionPayment::class);
    }

    /** Pesanan yang masih berjalan (belum final). */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    public function scopeBetween(Builder $query, mixed $start, mixed $end): Builder
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('invoice_code', 'like', "%{$term}%")
                ->orWhere('customer_name', 'like', "%{$term}%");
        });
    }

    public function canBePaid(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING], true)
            && $this->payment_status !== self::PAYMENT_PAID;
    }
}