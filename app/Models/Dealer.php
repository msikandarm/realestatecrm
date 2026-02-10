<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dealer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'cnic',
        'license_number',
        'default_commission_rate',
        'specialization',
        'status',
        'total_deals',
        'total_commission',
        'bank_name',
        'account_title',
        'account_number',
        'iban',
        'joined_date',
        'remarks',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'default_commission_rate' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'total_deals' => 'integer',
        'joined_date' => 'date',
    ];

    /**
     * Status constants.
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Specialization constants.
     */
    const SPEC_PLOTS = 'plots';
    const SPEC_RESIDENTIAL = 'residential';
    const SPEC_COMMERCIAL = 'commercial';
    const SPEC_ALL = 'all';

    /**
     * Get the user that owns the dealer profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all deals for this dealer.
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'dealer_id', 'user_id');
    }

    /**
     * Get account payments for this dealer (commission payments)
     */
    public function accountPayments()
    {
        return $this->morphMany(AccountPayment::class, 'payable');
    }

    /**
     * Get expenses related to this dealer
     */
    public function expenses()
    {
        return $this->morphMany(Expense::class, 'expensable');
    }

    /**
     * Scope a query to only include active dealers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include inactive dealers.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Scope a query to only include suspended dealers.
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', self::STATUS_SUSPENDED);
    }

    /**
     * Scope a query to filter by specialization.
     */
    public function scopeBySpecialization($query, string $specialization)
    {
        return $query->where('specialization', $specialization);
    }

    /**
     * Scope a query to get top performers.
     */
    public function scopeTopPerformers($query, int $limit = 10)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->orderBy('total_commission', 'desc')
                    ->limit($limit);
    }

    /**
     * Get total commission earned by this dealer.
     */
    public function getTotalCommissionAttribute(): float
    {
        return $this->deals()->sum('commission_amount') ?? 0.00;
    }

    /**
     * Get active deals count.
     */
    public function getActiveDealsCount(): int
    {
        return $this->deals()
                    ->whereIn('status', [Deal::STATUS_PENDING, Deal::STATUS_CONFIRMED])
                    ->count();
    }

    /**
     * Get completed deals count.
     */
    public function getCompletedDealsCount(): int
    {
        return $this->deals()
                    ->where('status', Deal::STATUS_COMPLETED)
                    ->count();
    }

    /**
     * Calculate earnings for a specific period.
     */
    public function calculateEarnings($startDate = null, $endDate = null): float
    {
        $query = $this->deals()->where('status', Deal::STATUS_COMPLETED);

        if ($startDate) {
            $query->where('completion_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('completion_date', '<=', $endDate);
        }

        return $query->sum('commission_amount') ?? 0.00;
    }

    /**
     * Get pending commission (from confirmed but not completed deals).
     */
    public function getPendingCommission(): float
    {
        return $this->deals()
                    ->where('status', Deal::STATUS_CONFIRMED)
                    ->sum('commission_amount') ?? 0.00;
    }

    /**
     * Get earned commission (from completed deals).
     */
    public function getEarnedCommission(): float
    {
        return $this->deals()
                    ->where('status', Deal::STATUS_COMPLETED)
                    ->sum('commission_amount') ?? 0.00;
    }

    /**
     * Update cached statistics.
     */
    public function updateStatistics(): void
    {
        $this->update([
            'total_deals' => $this->deals()->count(),
            'total_commission' => $this->getEarnedCommission(),
        ]);
    }

    /**
     * Check if dealer is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if dealer is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Activate dealer.
     */
    public function activate(): bool
    {
        return $this->update(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Deactivate dealer.
     */
    public function deactivate(): bool
    {
        return $this->update(['status' => self::STATUS_INACTIVE]);
    }

    /**
     * Suspend dealer.
     */
    public function suspend(): bool
    {
        return $this->update(['status' => self::STATUS_SUSPENDED]);
    }

    /**
     * Get dealer's performance rating (based on deals and commission).
     */
    public function getPerformanceRating(): string
    {
        $commission = $this->total_commission;

        if ($commission >= 1000000) {
            return 'Platinum';
        } elseif ($commission >= 500000) {
            return 'Gold';
        } elseif ($commission >= 200000) {
            return 'Silver';
        } elseif ($commission >= 50000) {
            return 'Bronze';
        }

        return 'Starter';
    }
}
