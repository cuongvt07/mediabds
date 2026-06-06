<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ListingResource;
use App\Models\ListingFavorite;
use App\Models\RealEstateListing;
use Illuminate\Http\Request;

class FavoriteApiController extends BaseApiController
{
    public function index()
    {
        $listingIds = ListingFavorite::where('user_id', auth()->id())->pluck('listing_id');

        $listings = RealEstateListing::query()
            ->whereIn('id', $listingIds)
            ->latest()
            ->get();

        return ListingResource::collection($listings);
    }

    public function toggle(Request $request)
    {
        $data = $request->validate([
            'listingId' => 'required',
        ]);

        $listingId = (int) $data['listingId'];
        $listing = RealEstateListing::find($listingId);
        if (! $listing) {
            return $this->fail('Không tìm thấy tin', 404);
        }

        $favorite = ListingFavorite::where('user_id', auth()->id())
            ->where('listing_id', $listingId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return $this->ok(['listingId' => (string) $listingId, 'favorited' => false]);
        }

        ListingFavorite::create([
            'user_id' => auth()->id(),
            'listing_id' => $listingId,
        ]);

        return $this->ok(['listingId' => (string) $listingId, 'favorited' => true]);
    }
}
