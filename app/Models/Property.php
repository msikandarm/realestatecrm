<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'reference_code',
        'type',
        'condition',
        'property_for',
        'plot_id',
        'society_id',
        'block_id',
        'street_id',
        'address',
        'area',
        'city',
        'province',
        'latitude',
        'longitude',
        'size',
        'size_unit',
        'size_in_sqft',
        'bedrooms',
        'bathrooms',
        'floors',
        'year_built',
        'furnished',
        'parking',
        'parking_spaces',
        'amenities',
        'features',
        'price',
        'rental_price',
        'rental_period',
        'price_per_unit',
        'negotiable',
        'owner_id',
        'owner_name',
        'owner_contact',
        'status',
        'featured',
        'is_verified',
        'views_count',
        'featured_image',
        'images',
        'documents',
        'video_url',
        'virtual_tour_url',
        'description',
        'remarks',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amenities' => 'array',
        'features' => 'array',
        'images' => 'array',
        'documents' => 'array',
        'size' => 'decimal:2',
        'size_in_sqft' => 'decimal:2',
        'price' => 'decimal:2',
        'rental_price' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'floors' => 'integer',
        'year_built' => 'integer',
        'parking_spaces' => 'integer',
        'views_count' => 'integer',
        'furnished' => 'boolean',
        'parking' => 'boolean',
        'negotiable' => 'boolean',
        'featured' => 'boolean',
        'is_verified' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Property belongs to a Plot (optional - if built on owned land)
     */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    /**
     * Get society
     */
    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    /**
     * Get block
     */
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * Get street
     */
    public function street(): BelongsTo
    {
        return $this->belongsTo(Street::class);
    }

    /**
     * Property belongs to a Client (owner)
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'owner_id');
    }

    /**
     * Property has many images
     */
    public function propertyImages(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('order');
    }

    /**
     * Get featured images
     */
    public function featuredImages(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->where('is_featured', true);
    }

    /**
     * Get deals for this property
     */
    public function deals()
    {
        return $this->morphMany(Deal::class, 'dealable');
    }

    /**
     * Get property files for this property
     */
    public function propertyFiles()
    {
        return $this->morphMany(PropertyFile::class, 'fileable');
    }

    /**
     * Get account payments for this property
     */
    public function accountPayments()
    {
        return $this->morphMany(AccountPayment::class, 'payable');
    }

    /**
     * Get expenses related to this property
     */
    public function expenses()
    {
        return $this->morphMany(Expense::class, 'expensable');
    }

    /**
     * Get creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Property updated by User
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ==================== ACCESSOR ATTRIBUTES ====================

    /**
     * Get full address attribute
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->area,
            $this->city,
        ]);

        // If property is on a plot, include plot address
        if ($this->plot) {
            array_unshift($parts, $this->plot->full_address);
        }

        return implode(', ', $parts);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'PKR ' . number_format($this->price, 0);
    }

    /**
     * Get formatted rental price
     */
    public function getFormattedRentalPriceAttribute(): string
    {
        if (!$this->rental_price) {
            return 'N/A';
        }
        $period = $this->rental_period === 'monthly' ? '/month' : '/year';
        return 'PKR ' . number_format($this->rental_price, 0) . $period;
    }

    // ==================== SCOPES ====================

    /**
     * Scope for available properties
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope for sold properties
     */
    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    /**
     * Scope for rented properties
     */
    public function scopeRented($query)
    {
        return $query->where('status', 'rented');
    }

    /**
     * Scope for featured properties
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope for verified properties
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope by property type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for houses
     */
    public function scopeHouses($query)
    {
        return $query->where('type', 'house');
    }

    /**
     * Scope for apartments
     */
    public function scopeApartments($query)
    {
        return $query->where('type', 'apartment');
    }

    /**
     * Scope for commercial properties
     */
    public function scopeCommercial($query)
    {
        return $query->where('type', 'commercial');
    }

    /**
     * Scope for new properties
     */
    public function scopeNewProperties($query)
    {
        return $query->where('condition', 'new');
    }

    /**
     * Scope for old properties
     */
    public function scopeOldProperties($query)
    {
        return $query->where('condition', 'old');
    }

    /**
     * Scope for furnished properties
     */
    public function scopeFurnished($query)
    {
        return $query->where('furnished', true);
    }

    /**
     * Scope for properties with parking
     */
    public function scopeWithParking($query)
    {
        return $query->where('parking', true);
    }

    /**
     * Scope for property type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for sale properties
     */
    public function scopeForSale($query)
    {
        return $query->whereIn('property_for', ['sale', 'both']);
    }

    /**
     * Scope for rent properties
     */
    public function scopeForRent($query)
    {
        return $query->whereIn('property_for', ['rent', 'both']);
    }

    /**
     * Scope by city
     */
    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope by owner
     */
    public function scopeByOwner($query, int $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    /**
     * Scope by price range
     */
    public function scopeByPriceRange($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
        return $query;
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if property is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Check if property is sold
     */
    public function isSold(): bool
    {
        return $this->status === 'sold';
    }

    /**
     * Check if property is rented
     */
    public function isRented(): bool
    {
        return $this->status === 'rented';
    }

    /**
     * Check if property is for sale
     */
    public function isForSale(): bool
    {
        return in_array($this->property_for, ['sale', 'both']);
    }

    /**
     * Check if property is for rent
     */
    public function isForRent(): bool
    {
        return in_array($this->property_for, ['rent', 'both']);
    }

    /**
     * Check if property is a house
     */
    public function isHouse(): bool
    {
        return $this->type === 'house';
    }

    /**
     * Check if property is an apartment
     */
    public function isApartment(): bool
    {
        return $this->type === 'apartment';
    }

    /**
     * Check if property is commercial
     */
    public function isCommercial(): bool
    {
        return $this->type === 'commercial';
    }

    /**
     * Check if property is new
     */
    public function isNew(): bool
    {
        return $this->condition === 'new';
    }

    /**
     * Increment views count
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Get property age
     */
    public function getAge(): ?int
    {
        return $this->year_built ? now()->year - $this->year_built : null;
    }

    /**
     * Convert size to square feet
     */
    public function getSizeInSquareFeet(): float
    {
        return match($this->size_unit) {
            'sq_m' => $this->size * 10.764, // 1 sq m = 10.764 sq ft
            'marla' => $this->size * 272.25, // 1 marla = 272.25 sq ft
            'kanal' => $this->size * 5445, // 1 kanal = 5445 sq ft
            default => $this->size, // sq_ft
        };
    }

    // ==================== BOOT METHOD ====================

    protected static function boot()
    {
        parent::boot();

        // Set created_by on creation
        static::creating(function ($property) {
            if (auth()->check() && !$property->created_by) {
                $property->created_by = auth()->id();
            }

            // Auto-calculate size in sqft if not set
            if (!$property->size_in_sqft && $property->size && $property->size_unit) {
                $property->size_in_sqft = match($property->size_unit) {
                    'sq_m' => $property->size * 10.764,
                    'marla' => $property->size * 272.25,
                    'kanal' => $property->size * 5445,
                    default => $property->size,
                };
            }
        });

        // Set updated_by on update
        static::updating(function ($property) {
            if (auth()->check()) {
                $property->updated_by = auth()->id();
            }

            // Recalculate size in sqft if size or unit changed
            if ($property->isDirty(['size', 'size_unit'])) {
                $property->size_in_sqft = match($property->size_unit) {
                    'sq_m' => $property->size * 10.764,
                    'marla' => $property->size * 272.25,
                    'kanal' => $property->size * 5445,
                    default => $property->size,
                };
            }
        });
    }
}
