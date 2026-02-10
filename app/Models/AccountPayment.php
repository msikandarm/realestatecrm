<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class AccountPayment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_number',
        'payment_type_id',
        'payable_type',
        'payable_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'bank_name',
        'account_number',
        'status',
        'clearance_date',
        'received_from',
        'contact_number',
        'purpose',
        'remarks',
        'documents',
        'received_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_date' => 'date',
        'clearance_date' => 'date',
        'amount' => 'decimal:2',
        'documents' => 'array',
    ];

    // Constants - Payment Methods
    const METHOD_CASH = 'cash';
    const METHOD_CHEQUE = 'cheque';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_ONLINE = 'online';
    const METHOD_CARD = 'card';
    const METHOD_OTHER = 'other';

    // Constants - Status
    const STATUS_PENDING = 'pending';
    const STATUS_RECEIVED = 'received';
    const STATUS_CLEARED = 'cleared';
    const STATUS_BOUNCED = 'bounced';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = $payment->generatePaymentNumber();
            }

            // Auto-clear cash payments
            if ($payment->payment_method === self::METHOD_CASH && $payment->status === self::STATUS_RECEIVED) {
                $payment->status = self::STATUS_CLEARED;
                $payment->clearance_date = $payment->payment_date;
            }
        });
    }

    /**
     * Get the payment type.
     */
    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    /**
     * Get the payable entity (Client, Deal, PropertyFile, Dealer, etc.).
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who received the payment.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Scope a query to only include received payments.
     */
    public function scopeReceived($query)
    {
        return $query->where('status', self::STATUS_RECEIVED);
    }

    /**
     * Scope a query to only include cleared payments.
     */
    public function scopeCleared($query)
    {
        return $query->where('status', self::STATUS_CLEARED);
    }

    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include bounced payments.
     */
    public function scopeBounced($query)
    {
        return $query->where('status', self::STATUS_BOUNCED);
    }

    /**
     * Scope a query to filter by payment type.
     */
    public function scopeByType($query, $typeId)
    {
        return $query->where('payment_type_id', $typeId);
    }

    /**
     * Scope a query to filter by payment method.
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by current month.
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year);
    }

    /**
     * Scope a query to filter by current year.
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereYear('payment_date', now()->year);
    }

    /**
     * Check if payment is received.
     */
    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }

    /**
     * Check if payment is cleared.
     */
    public function isCleared(): bool
    {
        return $this->status === self::STATUS_CLEARED;
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if payment is bounced.
     */
    public function isBounced(): bool
    {
        return $this->status === self::STATUS_BOUNCED;
    }

    /**
     * Check if payment is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if payment is cash.
     */
    public function isCash(): bool
    {
        return $this->payment_method === self::METHOD_CASH;
    }

    /**
     * Mark payment as received.
     */
    public function markAsReceived(): bool
    {
        $this->status = self::STATUS_RECEIVED;
        $this->payment_date = $this->payment_date ?? now();
        return $this->save();
    }

    /**
     * Mark payment as cleared.
     */
    public function markAsCleared(): bool
    {
        $this->status = self::STATUS_CLEARED;
        $this->clearance_date = now();
        return $this->save();
    }

    /**
     * Mark payment as bounced.
     */
    public function markAsBounced(?string $remarks = null): bool
    {
        $this->status = self::STATUS_BOUNCED;
        if ($remarks) {
            $this->remarks = $remarks;
        }
        return $this->save();
    }

    /**
     * Cancel payment.
     */
    public function cancel(?string $reason = null): bool
    {
        $this->status = self::STATUS_CANCELLED;
        if ($reason) {
            $this->remarks = $reason;
        }
        return $this->save();
    }

    /**
     * Generate unique payment number.
     */
    public function generatePaymentNumber(): string
    {
        $year = now()->year;
        $lastPayment = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastPayment ? intval(substr($lastPayment->payment_number, -6)) + 1 : 1;

        return 'PMT-' . $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'PKR ' . number_format($this->amount, 2);
    }

    /**
     * Get days since payment.
     */
    public function getDaysSincePaymentAttribute(): int
    {
        return $this->payment_date->diffInDays(now());
    }
}
