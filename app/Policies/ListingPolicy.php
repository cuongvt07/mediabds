<?php

namespace App\Policies;

use App\Models\RealEstateListing;
use App\Models\User;

class ListingPolicy
{
    /**
     * Determine whether the user can view any listings.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the listing.
     */
    public function view(User $user, RealEstateListing $listing): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create listings.
     */
    public function create(User $user): bool
    {
        return $user->isCtv();
    }

    /**
     * Determine whether the user can update the listing.
     */
    public function update(User $user, RealEstateListing $listing): bool
    {
        return $user->isAdmin() || $listing->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the listing.
     */
    public function delete(User $user, RealEstateListing $listing): bool
    {
        return $user->isAdmin();
    }
}
