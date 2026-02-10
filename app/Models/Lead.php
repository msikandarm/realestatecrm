<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'phone_secondary',
        'source',
        'referred_by',
        'interest_type',
        'society_id',
        'property_id',
        'plot_id',
        'budget_range',
        'preferred_location',
        'status',
        'priority',
        'assigned_to',
        'converted_to_client_id',
        'converted_at',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
    ];

    // ==================== CONSTANTS ====================

    const STATUS_NEW = 'new';
    const STATUS_CONTACTED = 'contacted';
    const STATUS_QUALIFIED = 'qualified';
    const STATUS_NEGOTIATION = 'negotiation';
    const STATUS_CONVERTED = 'converted';
    const STATUS_LOST = 'lost';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    const SOURCE_WEBSITE = 'website';
    const SOURCE_FACEBOOK = 'facebook';
    const SOURCE_REFERRAL = 'referral';
    const SOURCE_WALKIN = 'walk-in';
    const SOURCE_CALL = 'call';
    const SOURCE_WHATSAPP = 'whatsapp';
    const SOURCE_EMAIL = 'email';
    const SOURCE_OTHER = 'other';

    const INTEREST_PLOT = 'plot';
    const INTEREST_HOUSE = 'house';
    const INTEREST_APARTMENT = 'apartment';
    const INTEREST_COMMERCIAL = 'commercial';

    // ==================== RELATIONSHIPS ====================

    /**
     * Get assigned user (agent/dealer)
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get converted client
     */
    public function convertedToClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'converted_to_client_id');
    }

    /**
     * Get society of interest
     */
    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    /**
     * Get property of interest
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get plot of interest
     */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    /**
     * Get follow-ups for this lead
     */
    public function followUps(): MorphMany
    {
        return $this->morphMany(FollowUp::class, 'followable')->latest();
    }

    /**
     * Get pending follow-ups
     */
    public function pendingFollowUps(): MorphMany
    {
        return $this->morphMany(FollowUp::class, 'followable')
            ->where('status', 'pending')
            ->orderBy('follow_up_date');
    }

    /**
     * Get creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==================== SCOPES ====================

    /**
     * Scope for new leads
     */
    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    /**
     * Scope for contacted leads
     */
    public function scopeContacted($query)
    {
        return $query->where('status', self::STATUS_CONTACTED);
    }

    /**
     * Scope for qualified leads
     */
    public function scopeQualified($query)
    {
        return $query->where('status', self::STATUS_QUALIFIED);
    }

    /**
     * Scope for leads in negotiation
     */
    public function scopeNegotiation($query)
    {
        return $query->where('status', self::STATUS_NEGOTIATION);
    }

    /**
     * Scope for converted leads
     */
    public function scopeConverted($query)
    {
        return $query->where('status', self::STATUS_CONVERTED);
    }

    /**
     * Scope for lost leads
     */
    public function scopeLost($query)
    {
        return $query->where('status', self::STATUS_LOST);
    }

    /**
     * Scope for active leads (not converted or lost)
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_CONVERTED, self::STATUS_LOST]);
    }

    /**
     * Scope for high priority
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    /**
     * Scope for urgent priority
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
    }

    /**
     * Scope by source
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope by interest type
     */
    public function scopeByInterestType($query, string $type)
    {
        return $query->where('interest_type', $type);
    }

    /**
     * Scope by assigned user
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope for unassigned leads
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Scope for leads with pending follow-ups
     */
    public function scopeWithPendingFollowUps($query)
    {
        return $query->whereHas('followUps', function ($q) {
            $q->where('status', 'pending')
              ->where('follow_up_date', '<=', now());
        });
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if lead is new
     */
    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    /**
     * Check if lead is converted
     */
    public function isConverted(): bool
    {
        return $this->status === self::STATUS_CONVERTED;
    }

    /**
     * Check if lead is lost
     */
    public function isLost(): bool
    {
        return $this->status === self::STATUS_LOST;
    }

    /**
     * Check if lead is active
     */
    public function isActive(): bool
    {
        return !in_array($this->status, [self::STATUS_CONVERTED, self::STATUS_LOST]);
    }

    /**
     * Check if lead is hot (high priority + qualified/negotiation status)
     */
    public function isHot(): bool
    {
        return in_array($this->priority, [self::PRIORITY_HIGH, self::PRIORITY_URGENT])
            && in_array($this->status, [self::STATUS_QUALIFIED, self::STATUS_NEGOTIATION]);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_NEW => 'blue',
            self::STATUS_CONTACTED => 'yellow',
            self::STATUS_QUALIFIED => 'purple',
            self::STATUS_NEGOTIATION => 'orange',
            self::STATUS_CONVERTED => 'green',
            self::STATUS_LOST => 'red',
            default => 'gray',
        };
    }

    /**
     * Get priority badge color
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'gray',
            self::PRIORITY_MEDIUM => 'blue',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_URGENT => 'red',
            default => 'gray',
        };
    }

    /**
     * Get days since creation
     */
    public function getDaysSinceCreation(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Get follow-up count
     */
    public function getFollowUpCount(): int
    {
        return $this->followUps()->count();
    }

    /**
     * Get last follow-up date
     */
    public function getLastFollowUpDate(): ?string
    {
        $lastFollowUp = $this->followUps()->first();
        return $lastFollowUp?->follow_up_date?->format('Y-m-d');
    }

    /**
     * Get next follow-up date
     */
    public function getNextFollowUpDate(): ?string
    {
        $nextFollowUp = $this->pendingFollowUps()->first();
        return $nextFollowUp?->follow_up_date?->format('Y-m-d');
    }

    /**
     * Convert lead to client
     */
    public function convertToClient(Client $client): void
    {
        $this->update([
            'status' => self::STATUS_CONVERTED,
            'converted_to_client_id' => $client->id,
            'converted_at' => now(),
        ]);

        // Update client with lead tracking information
        $client->update([
            'converted_from_lead_id' => $this->id,
            'converted_from_lead_at' => now(),
            'lead_source' => $this->source,
        ]);
    }

    /**
     * Mark lead as lost
     */
    public function markAsLost(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_LOST,
            'remarks' => $reason ? $this->remarks . "\n\nLost: " . $reason : $this->remarks,
        ]);
    }

    /**
     * Assign lead to user
     */
    public function assignTo(User $user): void
    {
        $this->update(['assigned_to' => $user->id]);
    }

    /**
     * Update status
     */
    public function updateStatus(string $status): void
    {
        $this->update(['status' => $status]);
    }

    /**
     * Update priority
     */
    public function updatePriority(string $priority): void
    {
        $this->update(['priority' => $priority]);
    }

    // ==================== BOOT METHOD ====================

    protected static function boot()
    {
        parent::boot();

        // Set created_by on creation
        static::creating(function ($lead) {
            if (auth()->check() && !$lead->created_by) {
                $lead->created_by = auth()->id();
            }

            // Auto-assign to creator if not assigned
            if (auth()->check() && !$lead->assigned_to) {
                $lead->assigned_to = auth()->id();
            }
        });
    }
}
