<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Status constants.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    /**
     * Deal type constants.
     */
    const TYPE_PURCHASE = 'purchase';
    const TYPE_SALE = 'sale';
    const TYPE_BOOKING = 'booking';

    /**
     * Payment type constants.
     */
    const PAYMENT_CASH = 'cash';
    const PAYMENT_INSTALLMENT = 'installment';

    protected $fillable = [
        'deal_number',
        'client_id',
        'dealer_id',
        'dealable_type',
        'dealable_id',
        'deal_type',
        'deal_amount',
        'commission_amount',
        'commission_percentage',
        'payment_type',
        'installment_months',
        'down_payment',
        'monthly_installment',
        'status',
        'deal_date',
        'completion_date',
        'terms_conditions',
        'remarks',
        'documents',
        'created_by',
    ];

    protected $casts = [
        'documents' => 'array',
        'deal_date' => 'date',
        'completion_date' => 'date',
        'deal_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'installment_months' => 'integer',
    ];

    /**
     * Get the parent dealable model (Plot or Property)
     */
    public function dealable()
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
     * Get dealer/agent
     */
    public function dealer()
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }

    /**
     * Get property file
     */
    public function propertyFile()
    {
        return $this->hasOne(PropertyFile::class);
    }

    /**
     * Get creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get account payments for this deal
     */
    public function accountPayments()
    {
        return $this->morphMany(AccountPayment::class, 'payable');
    }

    /**
     * Get expenses related to this deal
     */
    public function expenses()
    {
        return $this->morphMany(Expense::class, 'expensable');
    }

    /**
     * Scope for confirmed deals
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope for completed deals
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for pending deals
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for cancelled deals
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope for deals by dealer
     */
    public function scopeByDealer($query, $dealerId)
    {
        return $query->where('dealer_id', $dealerId);
    }

    /**
     * Scope for deals by client
     */
    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope for deals this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('deal_date', date('m'))
                    ->whereYear('deal_date', date('Y'));
    }

    /**
     * Scope for deals this year
     */
    public function scopeThisYear($query)
    {
        return $query->whereYear('deal_date', date('Y'));
    }

    /**
     * Scope for cash deals
     */
    public function scopeCashDeals($query)
    {
        return $query->where('payment_type', self::PAYMENT_CASH);
    }

    /**
     * Scope for installment deals
     */
    public function scopeInstallmentDeals($query)
    {
        return $query->where('payment_type', self::PAYMENT_INSTALLMENT);
    }

    /**
     * Calculate commission based on percentage
     */
    public function calculateCommission(): float
    {
        if ($this->commission_percentage > 0) {
            return round(($this->deal_amount * $this->commission_percentage) / 100, 2);
        }

        return $this->commission_amount ?? 0.00;
    }

    /**
     * Auto-calculate and set commission
     */
    public function setCommission(): void
    {
        if ($this->commission_percentage > 0) {
            $this->commission_amount = $this->calculateCommission();
        }
    }

    /**
     * Calculate monthly installment
     */
    public function calculateMonthlyInstallment(): float
    {
        if ($this->payment_type === self::PAYMENT_INSTALLMENT && $this->installment_months > 0) {
            $remainingAmount = $this->deal_amount - ($this->down_payment ?? 0);
            return round($remainingAmount / $this->installment_months, 2);
        }

        return 0.00;
    }

    /**
     * Get remaining amount after down payment
     */
    public function getRemainingAmount(): float
    {
        return $this->deal_amount - ($this->down_payment ?? 0);
    }

    /**
     * Get payment schedule for installments
     */
    public function getPaymentSchedule(): array
    {
        if ($this->payment_type !== self::PAYMENT_INSTALLMENT) {
            return [];
        }

        $schedule = [];
        $startDate = $this->deal_date ?? now();

        for ($i = 1; $i <= $this->installment_months; $i++) {
            $schedule[] = [
                'installment_number' => $i,
                'due_date' => $startDate->copy()->addMonths($i)->format('Y-m-d'),
                'amount' => $this->monthly_installment,
                'status' => 'pending',
            ];
        }

        return $schedule;
    }

    /**
     * Check if deal is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if deal is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if deal is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if deal is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Confirm the deal
     */
    public function confirm(): bool
    {
        if ($this->isPending()) {
            return $this->update(['status' => self::STATUS_CONFIRMED]);
        }
        return false;
    }

    /**
     * Cancel the deal
     */
    public function cancel(string $reason = null): bool
    {
        $updateData = ['status' => self::STATUS_CANCELLED];

        if ($reason) {
            $updateData['remarks'] = ($this->remarks ? $this->remarks . "\n\n" : '')
                                    . "Cancellation Reason: " . $reason;
        }

        return $this->update($updateData);
    }

    /**
     * Complete the deal
     */
    public function complete(): bool
    {
        if ($this->isConfirmed()) {
            $updated = $this->update([
                'status' => self::STATUS_COMPLETED,
                'completion_date' => now(),
            ]);

            if ($updated && $this->dealer_id) {
                // Update dealer statistics
                $dealer = Dealer::where('user_id', $this->dealer_id)->first();
                if ($dealer) {
                    $dealer->updateStatistics();
                }
            }

            return $updated;
        }
        return false;
    }

    /**
     * Get deal age in days
     */
    public function getDealAge(): int
    {
        $startDate = $this->deal_date ?? $this->created_at;
        $endDate = $this->completion_date ?? now();

        return $startDate->diffInDays($endDate);
    }

    /**
     * Get completion status percentage
     */
    public function getCompletionStatus(): array
    {
        $status = [
            'status' => $this->status,
            'progress_percentage' => 0,
            'days_elapsed' => $this->getDealAge(),
        ];

        switch ($this->status) {
            case self::STATUS_PENDING:
                $status['progress_percentage'] = 25;
                break;
            case self::STATUS_CONFIRMED:
                $status['progress_percentage'] = 50;
                break;
            case self::STATUS_COMPLETED:
                $status['progress_percentage'] = 100;
                break;
            case self::STATUS_CANCELLED:
                $status['progress_percentage'] = 0;
                break;
        }

        return $status;
    }

    /**
     * Check if commission is paid (based on deal completion)
     */
    public function isCommissionEarned(): bool
    {
        return $this->isCompleted();
    }

    /**
     * Generate unique deal number
     */
    public static function generateDealNumber()
    {
        $prefix = 'DEAL';
        $year = date('Y');
        $lastDeal = self::whereYear('created_at', $year)
                        ->orderBy('id', 'desc')
                        ->first();

        $number = $lastDeal ? (int)substr($lastDeal->deal_number, -4) + 1 : 1;

        return $prefix . '-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
