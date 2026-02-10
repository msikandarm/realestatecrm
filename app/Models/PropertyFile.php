<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'file_number',
        'client_id',
        'fileable_type',
        'fileable_id',
        'deal_id',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'payment_plan',
        'total_installments',
        'installment_amount',
        'installment_frequency',
        'first_installment_date',
        'last_installment_date',
        'status',
        'issue_date',
        'completion_date',
        'is_transferred',
        'transferred_from_client',
        'transfer_date',
        'transfer_charges',
        'remarks',
        'documents',
        'created_by',
    ];

    protected $casts = [
        'documents' => 'array',
        'is_transferred' => 'boolean',
        'issue_date' => 'date',
        'completion_date' => 'date',
        'first_installment_date' => 'date',
        'last_installment_date' => 'date',
        'transfer_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'transfer_charges' => 'decimal:2',
        'total_installments' => 'integer',
    ];

    /**
     * Status constants.
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_TRANSFERRED = 'transferred';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_DEFAULTED = 'defaulted';

    /**
     * Payment plan constants.
     */
    const PLAN_CASH = 'cash';
    const PLAN_INSTALLMENT = 'installment';

    /**
     * Get the parent fileable model (Plot or Property)
     */
    public function fileable()
    {
        return $this->morphTo();
    }

    /**
     * Get client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get deal
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    /**
     * Get previous owner (for transfers)
     */
    public function transferredFromClient()
    {
        return $this->belongsTo(Client::class, 'transferred_from_client');
    }

    /**
     * Get installments
     */
    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    /**
     * Get file payments
     */
    public function filePayments()
    {
        return $this->hasMany(FilePayment::class);
    }

    /**
     * Get all payments (alias for filePayments)
     */
    public function payments()
    {
        return $this->hasMany(FilePayment::class);
    }

    /**
     * Get creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get account payments for this property file
     */
    public function accountPayments()
    {
        return $this->morphMany(AccountPayment::class, 'payable');
    }

    /**
     * Get expenses related to this property file
     */
    public function expenses()
    {
        return $this->morphMany(Expense::class, 'expensable');
    }

    /**
     * Scope for active files
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed files
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for transferred files
     */
    public function scopeTransferred($query)
    {
        return $query->where('status', self::STATUS_TRANSFERRED);
    }

    /**
     * Scope for defaulted files
     */
    public function scopeDefaulted($query)
    {
        return $query->where('status', self::STATUS_DEFAULTED);
    }

    /**
     * Scope for cancelled files
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope for files by client
     */
    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Get remaining balance
     */
    public function getRemainingBalance(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Get payment progress percentage
     */
    public function getPaymentProgress(): float
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        return round(($this->paid_amount / $this->total_amount) * 100, 2);
    }

    /**
     * Increment paid amount
     */
    public function incrementPaidAmount(float $amount): void
    {
        $this->paid_amount += $amount;
        $this->remaining_amount = $this->total_amount - $this->paid_amount;

        if ($this->remaining_amount <= 0) {
            $this->status = self::STATUS_COMPLETED;
            $this->completion_date = now();
        }

        $this->save();
    }

    /**
     * Decrement paid amount (for bounced/cancelled payments)
     */
    public function decrementPaidAmount(float $amount): void
    {
        $this->paid_amount -= $amount;
        $this->remaining_amount = $this->total_amount - $this->paid_amount;

        // If was completed, revert to active
        if ($this->status === self::STATUS_COMPLETED) {
            $this->status = self::STATUS_ACTIVE;
            $this->completion_date = null;
        }

        $this->save();
    }

    /**
     * Get total paid through file payments
     */
    public function calculateTotalPaid(): float
    {
        return $this->filePayments()
                    ->whereIn('status', [FilePayment::STATUS_RECEIVED, FilePayment::STATUS_CLEARED])
                    ->sum('amount');
    }

    /**
     * Recalculate and sync paid amount
     */
    public function syncPaidAmount(): void
    {
        $this->paid_amount = $this->calculateTotalPaid();
        $this->remaining_amount = $this->total_amount - $this->paid_amount;

        if ($this->remaining_amount <= 0 && $this->status === self::STATUS_ACTIVE) {
            $this->status = self::STATUS_COMPLETED;
            $this->completion_date = now();
        }

        $this->save();
    }

    /**
     * Get next installment due
     */
    public function getNextInstallmentDue()
    {
        return $this->filePayments()
                    ->where('payment_type', FilePayment::TYPE_INSTALLMENT)
                    ->where('status', FilePayment::STATUS_PENDING)
                    ->orderBy('due_date')
                    ->first();
    }

    /**
     * Get overdue payments
     */
    public function getOverduePayments()
    {
        return $this->filePayments()
                    ->overdue()
                    ->get();
    }

    /**
     * Get paid installments count
     */
    public function getPaidInstallmentsCount(): int
    {
        return $this->filePayments()
                    ->where('payment_type', FilePayment::TYPE_INSTALLMENT)
                    ->whereIn('status', [FilePayment::STATUS_RECEIVED, FilePayment::STATUS_CLEARED])
                    ->count();
    }

    /**
     * Get pending installments count
     */
    public function getPendingInstallmentsCount(): int
    {
        return $this->filePayments()
                    ->where('payment_type', FilePayment::TYPE_INSTALLMENT)
                    ->where('status', FilePayment::STATUS_PENDING)
                    ->count();
    }

    /**
     * Check if file is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->remaining_amount <= 0;
    }

    /**
     * Check if file is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if file is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if file has overdue payments
     */
    public function hasOverduePayments(): bool
    {
        return $this->filePayments()->overdue()->exists();
    }

    /**
     * Get installment schedule
     */
    public function getInstallmentSchedule(): array
    {
        if ($this->payment_plan !== self::PLAN_INSTALLMENT || !$this->total_installments) {
            return [];
        }

        $schedule = [];
        $startDate = $this->first_installment_date ?? $this->issue_date;
        $frequency = $this->installment_frequency ?? 'monthly';

        for ($i = 1; $i <= $this->total_installments; $i++) {
            $dueDate = match($frequency) {
                'monthly' => $startDate->copy()->addMonths($i - 1),
                'quarterly' => $startDate->copy()->addMonths(($i - 1) * 3),
                'yearly' => $startDate->copy()->addYears($i - 1),
                default => $startDate->copy()->addMonths($i - 1),
            };

            $payment = $this->filePayments()
                            ->where('installment_number', $i)
                            ->first();

            $schedule[] = [
                'installment_number' => $i,
                'due_date' => $dueDate->format('Y-m-d'),
                'amount' => $this->installment_amount,
                'status' => $payment ? $payment->status : 'pending',
                'paid_date' => $payment?->payment_date?->format('Y-m-d'),
                'payment_id' => $payment?->id,
            ];
        }

        return $schedule;
    }

    /**
     * Transfer file to new client
     */
    public function transferTo(Client $newClient, float $transferCharges = 0, string $remarks = null): bool
    {
        $this->transferred_from_client = $this->client_id;
        $this->client_id = $newClient->id;
        $this->transfer_date = now();
        $this->transfer_charges = $transferCharges;
        $this->is_transferred = true;
        $this->status = self::STATUS_TRANSFERRED;

        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n\n" : '') . "Transfer: " . $remarks;
        }

        return $this->save();
    }

    /**
     * Mark file as defaulted
     */
    public function markAsDefaulted(string $reason = null): bool
    {
        $this->status = self::STATUS_DEFAULTED;

        if ($reason) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n\n" : '') . "Defaulted: " . $reason;
        }

        return $this->save();
    }

    /**
     * Cancel file
     */
    public function cancel(string $reason = null): bool
    {
        $this->status = self::STATUS_CANCELLED;

        if ($reason) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n\n" : '') . "Cancelled: " . $reason;
        }

        return $this->save();
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus()
    {
        $this->remaining_amount = $this->total_amount - $this->paid_amount;

        if ($this->remaining_amount <= 0 && $this->status === self::STATUS_ACTIVE) {
            $this->status = self::STATUS_COMPLETED;
            $this->completion_date = now();
        }

        $this->save();
    }

    /**
     * Generate unique file number
     */
    public static function generateFileNumber()
    {
        $prefix = 'FILE';
        $year = date('Y');
        $lastFile = self::whereYear('created_at', $year)
                        ->orderBy('id', 'desc')
                        ->first();

        $number = $lastFile ? (int)substr($lastFile->file_number, -5) + 1 : 1;

        return $prefix . '-' . $year . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
