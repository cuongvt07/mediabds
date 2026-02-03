<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Admin phone number
     */
    public const ADMIN_PHONE = '0981847977';

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->phone === self::ADMIN_PHONE;
    }

    /**
     * Check if user has access to a property type
     */
    public function hasPropertyType($type): bool
    {
        if ($this->isAdmin())
            return true;
        return in_array($type, $this->property_types ?? []);
    }

    /**
     * Check if user can edit a listing
     */
    public function canEditListing($listing): bool
    {
        if ($this->isAdmin())
            return true;
        return $listing->user_id === $this->id;
    }

    /**
     * Check if user can delete a listing
     */
    public function canDeleteListing(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can edit a customer
     */
    public function canEditCustomer($customer): bool
    {
        if ($this->isAdmin())
            return true;
        return $customer->assigned_user_id === $this->id;
    }

    /**
     * Check if user can delete a customer
     */
    public function canDeleteCustomer(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Get customers assigned to this user
     */
    public function assignedCustomers(): HasMany
    {
        return $this->hasMany(Customer::class, 'assigned_user_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'license_key',
        'license_expires_at',
        'trial_ends_at',
        'property_types',
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
            'property_types' => 'array',
        ];
    }
}
