<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Installment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_file_id',
        'installment_number',
        'amount',
        'due_date',
        'paid_date',
        'status',
        'late_fee',
        'days_overdue',
        'payment_method',
        'reference_number',
        'remarks',
        'received_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    /**
     * Get property file
     */
    public function propertyFile()
    {
        return $this->belongsTo(PropertyFile::class);
    }

    /**
     * Get receiver (user who received payment)
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get payment
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Scope for pending installments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for paid installments
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for overdue installments
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
                    ->orWhere(function($q) {
                        $q->where('status', 'pending')
                          ->where('due_date', '<', today());
                    });
    }

    /**
     * Mark as paid
     */
    public function markAsPaid($paymentMethod = null, $referenceNumber = null, $receivedBy = null)
    {
        $this->update([
            'status' => 'paid',
            'paid_date' => now(),
            'payment_method' => $paymentMethod,
            'reference_number' => $referenceNumber,
            'received_by' => $receivedBy,
        ]);

        // Update property file payment status
        $this->propertyFile->paid_amount += $this->amount;
        $this->propertyFile->updatePaymentStatus();
    }

    /**
     * Calculate overdue days and late fee
     */
    public function calculateOverdue()
    {
        if ($this->status === 'pending' && $this->due_date < today()) {
            $this->days_overdue = today()->diffInDays($this->due_date);
            $this->status = 'overdue';

            // Calculate late fee (example: 1% per month)
            $monthsOverdue = ceil($this->days_overdue / 30);
            $this->late_fee = ($this->amount * 0.01) * $monthsOverdue;

            $this->save();
        }
    }
}
