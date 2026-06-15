<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\RealEstateListing;
use App\Models\SiteAmenity;
use App\Models\SiteBanner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RoomSiteController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = $this->publicRoomQuery();

        $districts = (clone $baseQuery)
            ->whereNotNull('district_id')
            ->whereNotNull('district_name')
            ->select('district_id', 'district_name')
            ->distinct()
            ->orderBy('district_name')
            ->get();

        $wards = collect();
        if ($request->filled('district')) {
            $wards = (clone $baseQuery)
                ->where('district_id', $request->string('district'))
                ->whereNotNull('ward_id')
                ->whereNotNull('ward_name')
                ->select('ward_id', 'ward_name')
                ->distinct()
                ->orderBy('ward_name')
                ->get();
        }

        $wardOptions = (clone $baseQuery)
            ->whereNotNull('district_id')
            ->whereNotNull('ward_id')
            ->whereNotNull('ward_name')
            ->select('district_id', 'ward_id', 'ward_name')
            ->distinct()
            ->orderBy('ward_name')
            ->get()
            ->groupBy('district_id')
            ->map(fn ($items) => $items->map(fn ($item) => [
                'id' => (string) $item->ward_id,
                'name' => $item->ward_name,
            ])->values());

        $query = clone $baseQuery;

        if ($request->filled('district')) {
            $query->where('district_id', (string) $request->input('district'));
        }
        if ($request->filled('ward')) {
            $query->where('ward_id', (string) $request->input('ward'));
        }
        if ($request->filled('room_type')) {
            $query->where('room_type', (string) $request->input('room_type'));
        }
        foreach ($this->selectedFurniture($request) as $furniture) {
            $query->whereJsonContains('amenities', $furniture);
        }
        foreach ($this->selectedAmenities($request) as $amenity) {
            $query->whereJsonContains('amenities', $amenity);
        }

        $priceExpression = $this->priceVndExpression();
        match ((string) $request->input('price')) {
            'low_high' => $query->orderByRaw("{$priceExpression} asc"),
            'high_low' => $query->orderByRaw("{$priceExpression} desc"),
            'under_3' => $query->whereRaw("{$priceExpression} < ?", [3000000]),
            '3_4' => $query->whereRaw("{$priceExpression} >= ? and {$priceExpression} <= ?", [3000000, 4000000]),
            '5_6' => $query->whereRaw("{$priceExpression} >= ? and {$priceExpression} <= ?", [5000000, 6000000]),
            'over_6' => $query->whereRaw("{$priceExpression} > ?", [6000000]),
            default => $query->latest('created_at'),
        };

        if (! in_array($request->input('price'), ['low_high', 'high_low'], true)) {
            $query->orderByDesc('id');
        }

        $listings = $query->paginate(12)->withQueryString();
        $slides = $this->bannerSlides();
        $usingSiteBanners = $slides->isNotEmpty();

        if (! $usingSiteBanners) {
            $slides = (clone $baseQuery)->latest('created_at')->limit(3)->get();
        }

        $amenityItems = $this->siteAmenities()->where('type', 'amenity')->values();
        $furnitureItems = $this->siteAmenities()->where('type', 'furniture')->values();

        return view('site.index', compact(
            'listings', 'slides', 'usingSiteBanners', 'districts', 'wards', 'wardOptions',
            'amenityItems', 'furnitureItems'
        ));
    }

    public function show(RealEstateListing $listing)
    {
        $allowed = $this->publicRoomQuery()->whereKey($listing->getKey())->exists();
        abort_unless($allowed, 404);

        if (Schema::hasColumn('real_estate_listings', 'view_count')) {
            $listing->increment('view_count');
        }

        $related = $this->publicRoomQuery()
            ->where('id', '<>', $listing->getKey())
            ->when($listing->district_id, fn (Builder $query) => $query->where('district_id', $listing->district_id))
            ->latest('created_at')
            ->limit(4)
            ->get();

        $amenities = $this->siteAmenities();

        return view('site.show', compact('listing', 'related', 'amenities'));
    }

    private function publicRoomQuery(): Builder
    {
        return RealEstateListing::query()
            ->where('is_sold', false)
            ->where(function (Builder $query) {
                $query->whereNull('status')->orWhere('status', 'active');
            })
            ->where(function (Builder $query) {
                $query->where('province_id', '79')
                    ->orWhere('province_name', 'like', '%Hồ Chí Minh%')
                    ->orWhere('province_name', 'like', '%HCM%');
            })
            ->where(function (Builder $query) {
                $query->where('property_type', 115)
                    ->orWhere('property_type', 'like', '%trọ%')
                    ->orWhereNotNull('room_type');
            });
    }

    private function priceVndExpression(): string
    {
        return "(case when price >= 1000000 then price "
            . "when price_unit in ('Tỷ', 'Tỉ', 'ty', '1') then price * 1000000000 "
            . "when price_unit in ('Triệu', 'trieu', '2') then price * 1000000 "
            . "else price end)";
    }

    private $amenitiesCache = null;

    private function siteAmenities()
    {
        if ($this->amenitiesCache !== null) {
            return $this->amenitiesCache;
        }

        if (! Schema::hasTable('site_amenities')) {
            return $this->amenitiesCache = collect();
        }

        return $this->amenitiesCache = SiteAmenity::query()->active()->ordered()->get();
    }

    private function selectedAmenities(Request $request): array
    {
        $allowed = $this->siteAmenities()->where('type', 'amenity')->pluck('key')->all();

        return array_values(array_intersect(
            $allowed,
            array_filter((array) $request->input('amenities', []))
        ));
    }

    private function selectedFurniture(Request $request): array
    {
        $allowed = $this->siteAmenities()->where('type', 'furniture')->pluck('key')->all();

        return array_values(array_intersect(
            $allowed,
            array_filter((array) $request->input('furniture', []))
        ));
    }

    private function bannerSlides()
    {
        if (! Schema::hasTable('site_banners')) {
            return collect();
        }

        return SiteBanner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(8)
            ->get();
    }
}
