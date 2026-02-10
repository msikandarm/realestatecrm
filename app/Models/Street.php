<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Street extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'block_id',
        'name',
        'code',
        'width',
        'length',
        'description',
        'type',
        'status',
        'total_plots',
        'available_plots',
        'sold_plots',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'width' => 'decimal:2',
        'length' => 'decimal:2',
        'total_plots' => 'integer',
        'available_plots' => 'integer',
        'sold_plots' => 'integer',
    ];

    /**
     * Relationships
     */

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function plots(): HasMany
    {
        return $this->hasMany(Plot::class);
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

    public function scopeByBlock($query, $blockId)
    {
        return $query->where('block_id', $blockId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCommercial($query)
    {
        return $query->where('type', 'commercial');
    }

    public function scopeResidential($query)
    {
        return $query->where('type', 'residential');
    }

    public function scopeWithAvailablePlots($query)
    {
        return $query->where('available_plots', '>', 0);
    }

    /**
     * Helper Methods
     */

    public function getFullNameAttribute()
    {
        return $this->block->society->name . ' - Block ' . $this->block->name . ' - ' . $this->name;
    }

    public function getSocietyAttribute()
    {
        return $this->block->society;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCommercial(): bool
    {
        return $this->type === 'commercial';
    }

    public function hasAvailablePlots(): bool
    {
        return $this->available_plots > 0;
    }

    /**
     * Update plot counts based on plots
     */
    public function updatePlotCounts()
    {
        $this->total_plots = $this->plots()->count();
        $this->available_plots = $this->plots()->where('status', 'available')->count();
        $this->sold_plots = $this->plots()->where('status', 'sold')->count();
        $this->save();

        // Update parent block counts
        $this->block->updatePlotCounts();
    }

    /**
     * Auto-generate code and update parent counts
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($street) {
            if (empty($street->code)) {
                // Generate code: "Street 1" -> "ST1"
                $street->code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $street->name), 0, 5));
            }
        });

        static::saved(function ($street) {
            // Update block's plot counts
            $street->block->updatePlotCounts();
        });

        static::deleted(function ($street) {
            // Update block when street is deleted
            $street->block->updatePlotCounts();
        });
    }
}
