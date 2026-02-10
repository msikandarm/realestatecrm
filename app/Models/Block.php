<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id',
        'name',
        'code',
        'total_area',
        'area_unit',
        'description',
        'status',
        'total_plots',
        'available_plots',
        'sold_plots',
        'map_file',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_area' => 'decimal:2',
        'total_plots' => 'integer',
        'available_plots' => 'integer',
        'sold_plots' => 'integer',
    ];

    /**
     * Relationships
     */

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function streets(): HasMany
    {
        return $this->hasMany(Street::class);
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

    public function scopeBySociety($query, $societyId)
    {
        return $query->where('society_id', $societyId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeWithAvailablePlots($query)
    {
        return $query->where('available_plots', '>', 0);
    }

    /**
     * Helper Methods
     */

    public function getTotalStreetsAttribute()
    {
        return $this->streets()->count();
    }

    public function getFullNameAttribute()
    {
        return $this->society->name . ' - Block ' . $this->name;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasAvailablePlots(): bool
    {
        return $this->available_plots > 0;
    }

    /**
     * Update plot counts when streets change
     */
    public function updatePlotCounts()
    {
        $this->total_plots = $this->streets()->sum('total_plots');
        $this->available_plots = $this->streets()->sum('available_plots');
        $this->sold_plots = $this->streets()->sum('sold_plots');
        $this->save();
    }

    /**
     * Auto-generate code and update parent counts
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($block) {
            if (empty($block->code)) {
                // Generate code: "A" -> "A", "Block A" -> "A"
                $block->code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $block->name), 0, 5));
            }
        });

        static::saved(function ($block) {
            // Update society's block count if needed
            $block->society->touch();
        });

        static::deleted(function ($block) {
            // Update society when block is deleted
            $block->society->touch();
        });
    }
}
