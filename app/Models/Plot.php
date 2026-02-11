<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plot extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'street_id',
        'society_id',
        'block_id',
        'plot_number',
        'plot_code',
        'size',
        'size_unit',
        'area',
        'area_unit',
        'length',
        'width',
        'type',
        'status',
        'price_per_marla',
        'total_price',
        'corner',
        'facing',
        'park_facing',
        'main_road_facing',
        'description',
        'features',
        'map_location',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'area' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'price_per_marla' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Relationships
     */

    public function street(): BelongsTo
    {
        return $this->belongsTo(Street::class);
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
     * Get property built on this plot (if any)
     */
    public function property()
    {
        return $this->hasOne(Property::class);
    }

    /**
     * Get all deals for this plot
     */
    public function deals()
    {
        return $this->morphMany(Deal::class, 'dealable');
    }

    /**
     * Get all property files for this plot
     */
    public function propertyFiles()
    {
        return $this->morphMany(PropertyFile::class, 'fileable');
    }

    /**
     * Scopes
     */

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    public function scopeBooked($query)
    {
        return $query->where('status', 'booked');
    }

    public function scopeByStreet($query, $streetId)
    {
        return $query->where('street_id', $streetId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeResidential($query)
    {
        return $query->where('type', 'residential');
    }

    public function scopeCommercial($query)
    {
        return $query->where('type', 'commercial');
    }

    public function scopeCorner($query)
    {
        return $query->where('corner', 'yes');
    }

    public function scopeParkFacing($query)
    {
        return $query->where('park_facing', 'yes');
    }

    /**
     * Helper Methods
     */

    public function getBlockAttribute()
    {
        return $this->street->block;
    }

    public function getSocietyAttribute()
    {
        return $this->street->block->society;
    }

    public function getFullAddressAttribute()
    {
        return sprintf(
            'Plot %s, %s, Block %s, %s',
            $this->plot_number,
            $this->street->name,
            $this->street->block->name,
            $this->street->block->society->name
        );
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isSold(): bool
    {
        return $this->status === 'sold';
    }

    public function isBooked(): bool
    {
        return $this->status === 'booked';
    }

    public function isCorner(): bool
    {
        return $this->corner === 'yes';
    }

    public function isParkFacing(): bool
    {
        return $this->park_facing === 'yes';
    }

    /**
     * Calculate total price based on area and price per marla
     */
    public function calculateTotalPrice()
    {
        if ($this->area && $this->price_per_marla) {
            // Convert area to marla if needed
            $areaInMarla = $this->convertToMarla($this->area, $this->area_unit);
            $this->total_price = $areaInMarla * $this->price_per_marla;
            $this->save();
        }
    }

    /**
     * Convert area to marla
     */
    private function convertToMarla($area, $unit)
    {
        return match($unit) {
            'marla' => $area,
            'kanal' => $area * 20,
            'acre' => $area * 160,
            'sq ft' => $area / 272.25,
            default => $area,
        };
    }

    /**
     * Auto-generate plot code and update parent counts
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plot) {
            if (empty($plot->plot_code)) {
                $street = Street::with('block.society')->find($plot->street_id);
                $plot->plot_code = sprintf(
                    '%s-%s-%s-%s',
                    $street->block->society->code,
                    $street->block->code,
                    $street->code,
                    $plot->plot_number
                );
            }

            // Calculate total price if not set
            if (!$plot->total_price && $plot->area && $plot->price_per_marla) {
                $areaInMarla = $plot->convertToMarla($plot->area, $plot->area_unit);
                $plot->total_price = $areaInMarla * $plot->price_per_marla;
            }
        });

        static::saved(function ($plot) {
            // Update street counts
            $plot->street->updatePlotCounts();
        });

        static::deleted(function ($plot) {
            // Update street counts
            $plot->street->updatePlotCounts();
        });
    }
}
