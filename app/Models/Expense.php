<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'expense_number',
        'payment_type_id',
        'expensable_type',
        'expensable_id',
        'amount',
        'expense_date',
        'payment_method',
        'reference_number',
        'bank_name',
        'account_number',
        'status',
        'payment_date',
        'clearance_date',
        'paid_to',
        'contact_number',
        'address',
        'tax_id',
        'description',
        'is_recurring',
        'recurring_frequency',
        'next_due_date',
        'tax_amount',
        'discount_amount',
        'net_amount',
        'remarks',
        'documents',
        'approved_by',
        'paid_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expense_date' => 'date',
        'payment_date' => 'date',
        'clearance_date' => 'date',
        'next_due_date' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'documents' => 'array',
    ];

    // Constants - Payment Methods
    const METHOD_CASH = 'cash';
    const METHOD_CHEQUE = 'cheque';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_ONLINE = 'online';
    const METHOD_CARD = 'card';
    const METHOD_CREDIT = 'credit';
    const METHOD_OTHER = 'other';

    // Constants - Status
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_CLEARED = 'cleared';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    // Constants - Recurring Frequency
    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_QUARTERLY = 'quarterly';
    const FREQUENCY_YEARLY = 'yearly';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = $expense->generateExpenseNumber();
            }

            // Calculate net amount
            if (is_null($expense->net_amount)) {
                $expense->net_amount = $expense->amount + $expense->tax_amount - $expense->discount_amount;
            }

            // Auto-clear cash payments
            if ($expense->payment_method === self::METHOD_CASH && $expense->status === self::STATUS_PAID) {
                $expense->status = self::STATUS_CLEARED;
                $expense->clearance_date = $expense->payment_date ?? $expense->expense_date;
            }

            // Set next due date for recurring expenses
            if ($expense->is_recurring && !$expense->next_due_date) {
                $expense->next_due_date = $expense->calculateNextDueDate();
            }
        });

        static::updating(function ($expense) {
            // Recalculate net amount if components change
            if ($expense->isDirty(['amount', 'tax_amount', 'discount_amount'])) {
                $expense->net_amount = $expense->amount + $expense->tax_amount - $expense->discount_amount;
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
     * Get the expensable entity (Property, Deal, Project, etc.).
     */
    public function expensable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who approved the expense.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who paid the expense.
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Scope a query to only include pending expenses.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include paid expenses.
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope a query to only include cleared expenses.
     */
    public function scopeCleared($query)
    {
        return $query->where('status', self::STATUS_CLEARED);
    }

    /**
     * Scope a query to only include recurring expenses.
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope a query to only include upcoming due expenses.
     */
    public function scopeUpcomingDue($query, $days = 7)
    {
        return $query->where('is_recurring', true)
                    ->where('next_due_date', '<=', now()->addDays($days))
                    ->where('next_due_date', '>=', now());
    }

    /**
     * Scope a query to only include overdue recurring expenses.
     */
    public function scopeOverdue($query)
    {
        return $query->where('is_recurring', true)
                    ->where('next_due_date', '<', now());
    }

    /**
     * Scope a query to filter by expense type.
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
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by current month.
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('expense_date', now()->month)
                    ->whereYear('expense_date', now()->year);
    }

    /**
     * Scope a query to filter by current year.
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereYear('expense_date', now()->year);
    }

    /**
     * Scope a query to filter by vendor.
     */
    public function scopeByVendor($query, $vendorName)
    {
        return $query->where('paid_to', 'like', "%{$vendorName}%");
    }

    /**
     * Check if expense is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if expense is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if expense is cleared.
     */
    public function isCleared(): bool
    {
        return $this->status === self::STATUS_CLEARED;
    }

    /**
     * Check if expense is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if expense is refunded.
     */
    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * Check if expense is recurring.
     */
    public function isRecurring(): bool
    {
        return $this->is_recurring;
    }

    /**
     * Check if expense is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->is_recurring && $this->next_due_date < now();
    }

    /**
     * Mark expense as paid.
     */
    public function markAsPaid(?int $paidBy = null): bool
    {
        $this->status = self::STATUS_PAID;
        $this->payment_date = now();
        if ($paidBy) {
            $this->paid_by = $paidBy;
        }
        return $this->save();
    }

    /**
     * Mark expense as cleared.
     */
    public function markAsCleared(): bool
    {
        $this->status = self::STATUS_CLEARED;
        $this->clearance_date = now();
        return $this->save();
    }

    /**
     * Cancel expense.
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
     * Mark expense as refunded.
     */
    public function markAsRefunded(?string $remarks = null): bool
    {
        $this->status = self::STATUS_REFUNDED;
        if ($remarks) {
            $this->remarks = $remarks;
        }
        return $this->save();
    }

    /**
     * Create next recurring expense.
     */
    public function createNextRecurrence(): ?self
    {
        if (!$this->is_recurring) {
            return null;
        }

        $nextExpense = $this->replicate(['expense_number']);
        $nextExpense->expense_date = $this->next_due_date;
        $nextExpense->payment_date = null;
        $nextExpense->clearance_date = null;
        $nextExpense->status = self::STATUS_PENDING;
        $nextExpense->next_due_date = $this->calculateNextDueDate($this->next_due_date);
        $nextExpense->save();

        // Update current expense's next due date
        $this->next_due_date = $nextExpense->next_due_date;
        $this->save();

        return $nextExpense;
    }

    /**
     * Calculate next due date based on frequency.
     */
    public function calculateNextDueDate(?Carbon $fromDate = null): Carbon
    {
        $date = $fromDate ?? $this->expense_date ?? now();

        return match ($this->recurring_frequency) {
            self::FREQUENCY_MONTHLY => $date->copy()->addMonth(),
            self::FREQUENCY_QUARTERLY => $date->copy()->addMonths(3),
            self::FREQUENCY_YEARLY => $date->copy()->addYear(),
            default => $date->copy()->addMonth(),
        };
    }

    /**
     * Generate unique expense number.
     */
    public function generateExpenseNumber(): string
    {
        $year = now()->year;
        $lastExpense = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastExpense ? intval(substr($lastExpense->expense_number, -6)) + 1 : 1;

        return 'EXP-' . $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'PKR ' . number_format($this->amount, 2);
    }

    /**
     * Get formatted net amount.
     */
    public function getFormattedNetAmountAttribute(): string
    {
        return 'PKR ' . number_format($this->net_amount, 2);
    }

    /**
     * Get days until next due.
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->is_recurring || !$this->next_due_date) {
            return null;
        }

        return now()->diffInDays($this->next_due_date, false);
    }
}
