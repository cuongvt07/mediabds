<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        return $this->isAdmin();
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
     * Get the user who invited this account.
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    /**
     * Get users invited by this account.
     */
    public function invitees(): HasMany
    {
        return $this->hasMany(User::class, 'invited_by_user_id');
    }

    /**
     * Get invitation logs sent by this account.
     */
    public function sentInviteLogs(): HasMany
    {
        return $this->hasMany(UserInvite::class, 'inviter_user_id');
    }

    /**
     * Get invite log that created this account.
     */
    public function inviteLog(): HasOne
    {
        return $this->hasOne(UserInvite::class, 'invited_user_id');
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
        'invite_code',
        'invited_by_user_id',
        'view_phone_pin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'view_phone_pin',
    ];

    /**
     * Get the user's current CTV rank.
     */
    public function getRankAttribute()
    {
        $invitesCount = $this->invitees()->count();
        $totalRevenue = RealEstateListingSale::where('sold_by_user_id', $this->id)->sum('revenue_amount');

        return \App\Models\CtvRank::query()
            ->where('min_invites', '<=', $invitesCount)
            ->whereRaw('min_price * 1000000000 <= ?', [$totalRevenue])
            ->orderByDesc('min_price')
            ->orderByDesc('min_invites')
            ->first();
    }

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
