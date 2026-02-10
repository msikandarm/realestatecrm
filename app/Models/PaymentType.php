<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PaymentType extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'is_active',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    // Constants
    const CATEGORY_INCOME = 'income';
    const CATEGORY_EXPENSE = 'expense';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($paymentType) {
            if (empty($paymentType->slug)) {
                $paymentType->slug = Str::slug($paymentType->name);
            }
        });
    }

    /**
     * Get the account payments for this payment type.
     */
    public function accountPayments(): HasMany
    {
        return $this->hasMany(AccountPayment::class);
    }

    /**
     * Get the expenses for this payment type.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Scope a query to only include active payment types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include income types.
     */
    public function scopeIncome($query)
    {
        return $query->where('category', self::CATEGORY_INCOME);
    }

    /**
     * Scope a query to only include expense types.
     */
    public function scopeExpense($query)
    {
        return $query->where('category', self::CATEGORY_EXPENSE);
    }

    /**
     * Scope a query to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Check if payment type is for income.
     */
    public function isIncome(): bool
    {
        return $this->category === self::CATEGORY_INCOME;
    }

    /**
     * Check if payment type is for expense.
     */
    public function isExpense(): bool
    {
        return $this->category === self::CATEGORY_EXPENSE;
    }
}
