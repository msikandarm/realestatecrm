<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Society extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'city_id',
        'province',
        'total_area',
        'area_unit',
        'description',
        'developer_name',
        'developer_contact',
        'status',
        'launch_date',
        'completion_date',
        'amenities',
        'map_file',
        'noc_file',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amenities' => 'array',
        'launch_date' => 'date',
        'completion_date' => 'date',
        'total_area' => 'decimal:2',
    ];

    /**
     * Relationships
     */

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scopes
     */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUnderDevelopment($query)
    {
        return $query->where('status', 'under-development');
    }

    /**
     * Helper Methods
     */

    public function getTotalBlocksAttribute()
    {
        return $this->blocks()->count();
    }

    public function getTotalPlotsAttribute()
    {
        // Compute total plots by counting plots linked to this society (robust if streets table lacks aggregate columns)
        return \App\Models\Plot::where('society_id', $this->id)->whereNull('deleted_at')->count() ?? 0;
    }

    public function getAvailablePlotsAttribute()
    {
        return \App\Models\Plot::where('society_id', $this->id)->where('status', 'available')->whereNull('deleted_at')->count() ?? 0;
    }

    public function getSoldPlotsAttribute()
    {
        return \App\Models\Plot::where('society_id', $this->id)->where('status', 'sold')->whereNull('deleted_at')->count() ?? 0;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Backwards-compatible alias: some views use possession_date field name.
     */
    public function getPossessionDateAttribute()
    {
        return $this->completion_date;
    }

    /**
     * Auto-generate code before creating
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($society) {
            if (empty($society->code)) {
                // Generate code from name: "DHA Phase 1" -> "DHA-P1"
                $society->code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $society->name), 0, 10));
            }
        });
    }
}
