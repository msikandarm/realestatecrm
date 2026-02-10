<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'receipt_number',
        'property_file_id',
        'client_id',
        'installment_id',
        'amount',
        'payment_type',
        'payment_method',
        'payment_date',
        'reference_number',
        'bank_name',
        'cheque_number',
        'status',
        'remarks',
        'documents',
        'received_by',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'documents' => 'array',
    ];

    /**
     * Get property file
     */
    public function propertyFile()
    {
        return $this->belongsTo(PropertyFile::class);
    }

    /**
     * Get client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get installment
     */
    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

    /**
     * Get receiver (user who received payment)
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for today's payments
     */
    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', today());
    }

    /**
     * Generate unique receipt number
     */
    public static function generateReceiptNumber()
    {
        $prefix = 'RCT';
        $year = date('Y');
        $lastPayment = self::whereYear('created_at', $year)
                          ->orderBy('id', 'desc')
                          ->first();

        $number = $lastPayment ? (int)substr($lastPayment->receipt_number, -6) + 1 : 1;

        return $prefix . '-' . $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
