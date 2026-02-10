<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'cnic',
        'profile_picture',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relationships
     */

    // User as Dealer (one-to-one with dealer profile)
    public function dealer()
    {
        return $this->hasOne(Dealer::class);
    }

    // Alias for dealer profile
    public function dealerProfile()
    {
        return $this->hasOne(Dealer::class);
    }

    // Get assigned clients
    public function assignedClients()
    {
        return $this->hasMany(Client::class, 'assigned_to');
    }

    // Get assigned leads
    public function assignedLeads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    // Deals created by this user
    public function createdDeals()
    {
        return $this->hasMany(Deal::class, 'created_by');
    }

    // Deals where this user is the dealer (earning commission)
    public function dealerDeals()
    {
        return $this->hasMany(Deal::class, 'dealer_id');
    }

    // Payments received by this user
    public function receivedPayments()
    {
        return $this->hasMany(Payment::class, 'received_by');
    }

    // Follow-ups assigned to this user
    public function followUps()
    {
        return $this->hasMany(FollowUp::class, 'assigned_to');
    }

    /**
     * Helper Methods
     */

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isDealer(): bool
    {
        return $this->hasRole('dealer');
    }

    public function isAccountant(): bool
    {
        return $this->hasRole('accountant');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    public function canAccessAdmin(): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin', 'manager']);
    }

    public function canAccessReports(): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin', 'manager', 'accountant']);
    }

    /**
     * Scopes
     */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->role($role);
    }

    public function scopeDealers($query)
    {
        return $query->role('dealer');
    }

    public function scopeAccountants($query)
    {
        return $query->role('accountant');
    }
}
