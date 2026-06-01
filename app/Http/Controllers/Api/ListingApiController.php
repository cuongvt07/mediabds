<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CreateListingRequest;
use App\Http\Resources\ListingResource;
use App\Models\RealEstateListing;
use Illuminate\Http\Request;

class ListingApiController extends BaseApiController
{
    /**
     * Browse public listings (available for guests).
     */
    public function index(Request $req)
    {
        $perPage = min((int) $req->integer('per_page', 12), 30);
        if ($perPage < 1) {
            $perPage = 12;
        }

        $query = RealEstateListing::query()->where('is_sold', false);

        // Filter: type (Bán/Cho thuê)
        if ($req->filled('type')) {
            $query->where('type', $req->string('type'));
        }

        // Filter: property_type
        if ($req->filled('property_type')) {
            $query->where('property_type', $req->string('property_type'));
        }

        // Filter: province / district / ward
        // FE có thể gửi mã (province_id) hoặc tên (province_name) → khớp cả hai cho an toàn.
        if ($req->filled('province')) {
            $v = (string) $req->string('province');
            $query->where(fn ($q) => $q->where('province_id', $v)->orWhere('province_name', 'like', "%{$v}%"));
        }
        if ($req->filled('district')) {
            $v = (string) $req->string('district');
            $query->where(fn ($q) => $q->where('district_id', $v)->orWhere('district_name', 'like', "%{$v}%"));
        }
        if ($req->filled('ward')) {
            $v = (string) $req->string('ward');
            $query->where(fn ($q) => $q->where('ward_id', $v)->orWhere('ward_name', 'like', "%{$v}%"));
        }

        // Filter: bedrooms
        if ($req->filled('bedrooms')) {
            $query->where('bedrooms', '>=', (int) $req->integer('bedrooms'));
        }

        // Filter: direction
        if ($req->filled('direction')) {
            $query->where('direction', $req->string('direction'));
        }

        // Filter: area range (m²)
        if ($req->filled('min_area')) {
            $query->where('area', '>=', (float) $req->input('min_area'));
        }
        if ($req->filled('max_area')) {
            $query->where('area', '<=', (float) $req->input('max_area'));
        }

        // Filter: price range (TỶ → VNĐ) — chuẩn hoá unit qua SQL CASE
        // (Giống logic đã fix trong search_listings của Chatbot)
        if ($req->filled('min_price') || $req->filled('max_price')) {
            $priceExpr = "price * CASE price_unit "
                . "WHEN 'Tỷ' THEN 1000000000 "
                . "WHEN 'Tỉ' THEN 1000000000 "
                . "WHEN 'Triệu' THEN 1000000 "
                . "ELSE 1 END";

            if ($req->filled('min_price')) {
                $minVnd = (float) $req->input('min_price') * 1_000_000_000;
                $query->whereRaw("({$priceExpr}) >= ?", [$minVnd]);
            }
            if ($req->filled('max_price')) {
                $maxVnd = (float) $req->input('max_price') * 1_000_000_000;
                $query->whereRaw("({$priceExpr}) <= ?", [$maxVnd]);
            }
        }

        // Sorting
        $sortBy = $req->input('sort_by', 'created_at');
        $sortOrder = $req->input('sort_order', 'desc');

        $allowedSortBy = ['created_at', 'price', 'area'];
        if (!in_array($sortBy, $allowedSortBy, true)) {
            $sortBy = 'created_at';
        }
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortOrder);

        $page = $query->paginate($perPage)->appends($req->query());

        return ListingResource::collection($page);
    }

    /**
     * Get details for a single listing (by id or code).
     */
    public function show($idOrCode)
    {
        $listing = RealEstateListing::with([
            'user:id,name,phone,avatar',
            'reporter:id,name',
        ])
            ->where('id', $idOrCode)
            ->orWhere('code', $idOrCode)
            ->first();

        if (!$listing) {
            return $this->fail('Không tìm thấy tin', 404);
        }

        return new ListingResource($listing);
    }

    /**
     * Create a new listing (auth required).
     */
    public function store(CreateListingRequest $req)
    {
        $this->authorize('create', RealEstateListing::class);

        $data = $req->validated();
        $data['user_id'] = auth()->id();
        $data['code'] = 'API-' . strtoupper(str()->random(6));

        $listing = RealEstateListing::create($data);

        return $this->ok(new ListingResource($listing), 'Created', 201);
    }

    /**
     * Update an existing listing (auth required, owner or admin).
     */
    public function update(CreateListingRequest $req, $id)
    {
        $listing = RealEstateListing::findOrFail($id);
        $this->authorize('update', $listing);

        $listing->update($req->validated());

        return $this->ok(new ListingResource($listing->fresh()));
    }

    /**
     * Delete a listing (admin only).
     */
    public function destroy($id)
    {
        $listing = RealEstateListing::findOrFail($id);
        $this->authorize('delete', $listing);

        $listing->delete();

        return $this->ok(null, 'Deleted');
    }
}
