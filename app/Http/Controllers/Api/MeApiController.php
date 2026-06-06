<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ListingResource;
use App\Http\Resources\UserResource;
use App\Models\RealEstateListing;
use Illuminate\Http\Request;

class MeApiController extends BaseApiController
{
    /**
     * Return the currently authenticated user as a UserResource.
     */
    public function show()
    {
        return new UserResource(auth()->user());
    }

    /**
     * Return paginated listings owned by the current user.
     */
    public function myListings(Request $req)
    {
        $page = RealEstateListing::where('user_id', auth()->id())
            ->with('user:id,name,phone')
            ->orderBy('created_at', 'desc')
            ->paginate($req->integer('per_page', 10));

        return ListingResource::collection($page);
    }

    /**
     * Return aggregated stats for the current user.
     */
    public function myStats()
    {
        $user = auth()->user();

        return $this->ok([
            'total_revenue' => (float) $user->total_revenue,
            'invites_count' => $user->invitees()->count(),
            'rank' => $user->rank ? ['name' => $user->rank->name, 'min_price' => $user->rank->min_price] : null,
            'listings_count' => \App\Models\RealEstateListing::where('user_id', $user->id)->count(),
            'listings_sold' => \App\Models\RealEstateListing::where('user_id', $user->id)->where('is_sold', true)->count(),
        ]);
    }
}
