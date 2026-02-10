<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'phone_secondary',
        'cnic',
        'address',
        'city',
        'province',
        'client_type',
        'client_status',
        'occupation',
        'company',
        'remarks',
        'assigned_to',
        'documents',
        'created_by',
        'converted_from_lead_id',
        'converted_from_lead_at',
        'lead_source',
    ];

    protected $casts = [
        'documents' => 'array',
        'converted_from_lead_at' => 'datetime',
    ];

    /**
     * Get assigned dealer/agent
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get deals
     */
    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Get property files
     */
    public function propertyFiles()
    {
        return $this->hasMany(PropertyFile::class);
    }

    /**
     * Get payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get properties owned by client
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    /**
     * Get follow-ups
     */
    public function followUps()
    {
        return $this->morphMany(FollowUp::class, 'followable');
    }

    /**
     * Get account payments for this client
     */
    public function accountPayments()
    {
        return $this->morphMany(AccountPayment::class, 'payable');
    }

    /**
     * Get expenses related to this client
     */
    public function expenses()
    {
        return $this->morphMany(Expense::class, 'expensable');
    }

    /**the original lead this client was converted from
     */
    public function originalLead()
    {
        return $this->belongsTo(Lead::class, 'converted_from_lead_id');
    }

    /**
     * Get all leads that were converted to this client
     * (in case of merging multiple leads into one client)
     * Get converted leads
     */
    public function convertedFromLeads()
    {
        return $this->hasMany(Lead::class, 'converted_to_client_id');
    }

    /**
     * Scope for active clients
     */
    public function scopeActive($query)
    {
        return $query->where('client_status', 'active');
    }

    /**
     * Scope for buyers
     */
    public function scopeBuyers($query)
    {
        return $query->whereIn('client_type', ['buyer', 'both']);
    }

    /**
     * Scope for sellers
     */
    public function scopeSellers($query)
    {
        return $query->whereIn('client_type', ['seller', 'both']);
    }

    /**
     * Scope for clients converted from leads
     */
    public function scopeConvertedFromLead($query)
    {
        return $query->whereNotNull('converted_from_lead_id');
    }

    /**
     * Scope for direct clients (not converted from leads)
     */
    public function scopeDirectClients($query)
    {
        return $query->whereNull('converted_from_lead_id');
    }

    /**
     * Scope by lead source
     */
    public function scopeByLeadSource($query, string $source)
    {
        return $query->where('lead_source', $source);
    }

    /**
     * Check if client was converted from a lead
     */
    public function isConvertedFromLead(): bool
    {
        return !is_null($this->converted_from_lead_id);
    }

    /**
     * Check if client is a direct client (not from lead)
     */
    public function isDirectClient(): bool
    {
        return is_null($this->converted_from_lead_id);
    }

    /**
     * Get days since conversion from lead
     */
    public function getDaysSinceConversion(): ?int
    {
        if (!$this->converted_from_lead_at) {
            return null;
        }

        return now()->diffInDays($this->converted_from_lead_at);
    }

    /**
     * Get lead source badge color
     */
    public function getLeadSourceColorAttribute(): string
    {
        return match($this->lead_source) {
            'website' => 'primary',
            'facebook' => 'info',
            'referral' => 'success',
            'walk-in' => 'warning',
            'call' => 'secondary',
            'whatsapp' => 'success',
            'email' => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Get client status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->client_status) {
            'active' => 'success',
            'inactive' => 'warning',
            'blacklisted' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get client type label
     */
    public function getTypeLabel(): string
    {
        return match($this->client_type) {
            'buyer' => 'Buyer',
            'seller' => 'Seller',
            'both' => 'Buyer & Seller',
            default => ucfirst($this->client_type),
        };
    }

    /**
     * Check if client is a buyer
     */
    public function isBuyer(): bool
    {
        return in_array($this->client_type, ['buyer', 'both']);
    }

    /**
     * Check if client is a seller
     */
    public function isSeller(): bool
    {
        return in_array($this->client_type, ['seller', 'both']);
    }

    /**
     * Check if client is active
     */
    public function isActive(): bool
    {
        return $this->client_status === 'active';
    }

    /**
     * Check if client is blacklisted
     */
    public function isBlacklisted(): bool
    {
        return $this->client_status === 'blacklisted';
    }

    /**
     * Get total deals value
     */
    public function getTotalDealsValue(): float
    {
        return $this->deals()->sum('total_amount') ?? 0;
    }

    /**
     * Get active deals count
     */
    public function getActiveDealsCount(): int
    {
        return $this->deals()->whereIn('deal_status', ['in_progress', 'pending'])->count();
    }

    /**
     * Get completed deals count
     */
    public function getCompletedDealsCount(): int
    {
        return $this->deals()->where('deal_status', 'completed')->count();
    }
}
