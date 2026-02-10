<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FilePayment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'property_file_id',
        'payment_number',
        'amount',
        'payment_date',
        'due_date',
        'payment_type',
        'payment_method',
        'reference_number',
        'bank_name',
        'installment_number',
        'status',
        'clearance_date',
        'penalty_amount',
        'discount_amount',
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
        'due_date' => 'date',
        'clearance_date' => 'date',
        'amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'installment_number' => 'integer',
        'documents' => 'array',
    ];

    /**
     * Payment type constants.
     */
    const TYPE_DOWN_PAYMENT = 'down_payment';
    const TYPE_INSTALLMENT = 'installment';
    const TYPE_PARTIAL = 'partial';
    const TYPE_FULL_PAYMENT = 'full_payment';
    const TYPE_TRANSFER_CHARGES = 'transfer_charges';
    const TYPE_PENALTY = 'penalty';
    const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * Payment method constants.
     */
    const METHOD_CASH = 'cash';
    const METHOD_CHEQUE = 'cheque';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_ONLINE = 'online';
    const METHOD_CARD = 'card';

    /**
     * Status constants.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_RECEIVED = 'received';
    const STATUS_CLEARED = 'cleared';
    const STATUS_BOUNCED = 'bounced';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the property file that owns the payment.
     */
    public function propertyFile(): BelongsTo
    {
        return $this->belongsTo(PropertyFile::class);
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
    public function scopeByType($query, string $type)
    {
        return $query->where('payment_type', $type);
    }

    /**
     * Scope a query to filter by payment method.
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope a query to get overdue payments.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where('due_date', '<', now());
    }

    /**
     * Get the net payment amount (after penalties and discounts).
     */
    public function getNetAmount(): float
    {
        return $this->amount + $this->penalty_amount - $this->discount_amount;
    }

    /**
     * Check if payment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->due_date
            && $this->due_date->isPast();
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
     * Check if payment is bounced.
     */
    public function isBounced(): bool
    {
        return $this->status === self::STATUS_BOUNCED;
    }

    /**
     * Mark payment as received.
     */
    public function markAsReceived(): bool
    {
        return $this->update(['status' => self::STATUS_RECEIVED]);
    }

    /**
     * Mark payment as cleared.
     */
    public function markAsCleared(): bool
    {
        $updated = $this->update([
            'status' => self::STATUS_CLEARED,
            'clearance_date' => now(),
        ]);

        if ($updated) {
            // Update property file paid amount
            $this->propertyFile->incrementPaidAmount($this->getNetAmount());
        }

        return $updated;
    }

    /**
     * Mark payment as bounced.
     */
    public function markAsBounced(string $reason = null): bool
    {
        $updateData = ['status' => self::STATUS_BOUNCED];

        if ($reason) {
            $updateData['remarks'] = ($this->remarks ? $this->remarks . "\n\n" : '')
                                    . "Bounced: " . $reason;
        }

        $updated = $this->update($updateData);

        if ($updated) {
            // Reverse the payment if it was already counted
            if ($this->wasChanged('status') && $this->getOriginal('status') === self::STATUS_CLEARED) {
                $this->propertyFile->decrementPaidAmount($this->getNetAmount());
            }
        }

        return $updated;
    }

    /**
     * Cancel payment.
     */
    public function cancel(string $reason = null): bool
    {
        $updateData = ['status' => self::STATUS_CANCELLED];

        if ($reason) {
            $updateData['remarks'] = ($this->remarks ? $this->remarks . "\n\n" : '')
                                    . "Cancelled: " . $reason;
        }

        return $this->update($updateData);
    }

    /**
     * Calculate penalty for late payment.
     */
    public function calculatePenalty(float $penaltyRate = 0): float
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        $daysLate = $this->due_date->diffInDays(now());
        $penalty = ($this->amount * $penaltyRate / 100) * $daysLate;

        return round($penalty, 2);
    }

    /**
     * Generate unique payment number.
     */
    public static function generatePaymentNumber(): string
    {
        $prefix = 'PAY';
        $year = date('Y');
        $lastPayment = self::whereYear('created_at', $year)
                        ->orderBy('id', 'desc')
                        ->first();

        $number = $lastPayment ? (int)substr($lastPayment->payment_number, -6) + 1 : 1;

        return $prefix . '-' . $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = self::generatePaymentNumber();
            }
        });

        static::created(function ($payment) {
            // Auto-update property file if payment is already cleared
            if ($payment->status === self::STATUS_CLEARED) {
                $payment->propertyFile->incrementPaidAmount($payment->getNetAmount());
            }
        });
    }
}
