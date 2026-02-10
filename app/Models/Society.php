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
        return $this->blocks()->withSum('streets', 'total_plots')->get()->sum('streets_sum_total_plots') ?? 0;
    }

    public function getAvailablePlotsAttribute()
    {
        return $this->blocks()->withSum('streets', 'available_plots')->get()->sum('streets_sum_available_plots') ?? 0;
    }

    public function getSoldPlotsAttribute()
    {
        return $this->blocks()->withSum('streets', 'sold_plots')->get()->sum('streets_sum_sold_plots') ?? 0;
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
