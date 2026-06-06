<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CreateListingRequest;
use App\Http\Resources\ListingResource;
use App\Models\ListingViewEvent;
use App\Models\RealEstateListing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ListingApiController extends BaseApiController
{
    public function index(Request $req)
    {
        $perPage = min((int) $req->integer('per_page', 12), 30);
        if ($perPage < 1) {
            $perPage = 12;
        }

        $query = RealEstateListing::query()
            ->with('user:id,name,phone,avatar')
            ->where('is_sold', false)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            });

        if ($req->filled('q')) {
            $term = trim((string) $req->string('q'));
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('address', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%");
            });
        }

        if ($req->filled('category_id')) {
            $query->where('category_id', $req->string('category_id'));
        }

        if ($req->filled('type')) {
            $query->where('type', $req->string('type'));
        }

        if ($req->filled('property_type')) {
            $query->where('property_type', $req->string('property_type'));
        }

        if ($req->filled('province')) {
            $v = (string) $req->string('province');
            $query->where(function ($q) use ($v) {
                $q->where('province_id', $v)->orWhere('province_name', 'like', "%{$v}%");
            });
        }
        if ($req->filled('district')) {
            $v = (string) $req->string('district');
            $query->where(function ($q) use ($v) {
                $q->where('district_id', $v)->orWhere('district_name', 'like', "%{$v}%");
            });
        }
        if ($req->filled('ward')) {
            $v = (string) $req->string('ward');
            $query->where(function ($q) use ($v) {
                $q->where('ward_id', $v)->orWhere('ward_name', 'like', "%{$v}%");
            });
        }

        if ($req->filled('bedrooms')) {
            $query->where('bedrooms', '>=', (int) $req->integer('bedrooms'));
        }

        if ($req->filled('direction')) {
            $query->where('direction', $req->string('direction'));
        }

        if ($req->filled('furnish')) {
            $query->where('furnish', $req->string('furnish'));
        }

        if ($req->boolean('vip_only')) {
            $query->where('vip_tier', '<>', 'normal');
        }

        if ($req->filled('min_area')) {
            $query->where('area', '>=', (float) $req->input('min_area'));
        }
        if ($req->filled('max_area')) {
            $query->where('area', '<=', (float) $req->input('max_area'));
        }

        if ($req->filled('min_price') || $req->filled('max_price')) {
            $priceExpr = $this->priceVndExpression();

            if ($req->filled('min_price')) {
                $minVnd = (float) $req->input('min_price') * 1000000000;
                $query->whereRaw("({$priceExpr}) >= ?", [$minVnd]);
            }
            if ($req->filled('max_price')) {
                $maxVnd = (float) $req->input('max_price') * 1000000000;
                $query->whereRaw("({$priceExpr}) <= ?", [$maxVnd]);
            }
        }

        $sortBy = $req->input('sort_by', 'created_at');
        $sortOrder = strtolower((string) $req->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (! in_array($sortBy, ['created_at', 'price', 'area', 'view_count'], true)) {
            $sortBy = 'created_at';
        }

        if ($sortBy === 'price') {
            $query->orderByRaw('(' . $this->priceVndExpression() . ') ' . $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        return ListingResource::collection($query->paginate($perPage)->appends($req->query()));
    }

    public function show($idOrCode)
    {
        $listing = RealEstateListing::with([
            'user:id,name,phone,avatar',
            'reporter:id,name',
        ])
            ->where(function ($q) use ($idOrCode) {
                if (is_numeric($idOrCode)) {
                    $q->where('id', (int) $idOrCode);
                }

                $q->orWhere('code', $idOrCode)
                    ->orWhere('slug', $idOrCode);

                if (preg_match('/-(\d+)$/', (string) $idOrCode, $m)) {
                    $q->orWhere('id', (int) $m[1]);
                }
            })
            ->first();

        if (! $listing) {
            return $this->fail('Không tìm thấy tin', 404);
        }

        $listing->increment('view_count');

        try {
            ListingViewEvent::create([
                'listing_id' => $listing->id,
                'user_id' => auth()->id(),
                'ip_hash' => request()->ip() ? hash('sha256', request()->ip()) : null,
                'user_agent' => Str::limit((string) request()->userAgent(), 255, ''),
            ]);
        } catch (\Throwable $e) {
            // Analytics must not block listing details.
        }

        return new ListingResource($listing->fresh(['user:id,name,phone,avatar', 'reporter:id,name']));
    }

    public function store(CreateListingRequest $req)
    {
        $this->authorize('create', RealEstateListing::class);

        $data = $this->normalizeListingPayload($req->validated());
        $data['user_id'] = auth()->id();
        $data['code'] = $this->makeCode($data['property_type'] ?? null);
        $data['slug'] = $this->makeSlug($data['title']);
        $data['status'] = $data['status'] ?? 'active';
        $data['published_at'] = $data['published_at'] ?? now();
        $data['expires_at'] = $data['expires_at'] ?? now()->addDays(60);

        $listing = RealEstateListing::create($data);

        return $this->ok(new ListingResource($listing->fresh('user')), 'Created', 201);
    }

    public function update(CreateListingRequest $req, $id)
    {
        $listing = RealEstateListing::findOrFail($id);
        $this->authorize('update', $listing);

        $listing->update($this->normalizeListingPayload($req->validated(), $listing));

        return $this->ok(new ListingResource($listing->fresh('user')));
    }

    public function destroy($id)
    {
        $listing = RealEstateListing::findOrFail($id);
        $this->authorize('delete', $listing);

        $listing->delete();

        return $this->ok(null, 'Deleted');
    }

    private function priceVndExpression(): string
    {
        return "CASE "
            . "WHEN price >= 1000000 THEN price "
            . "WHEN price_unit IN ('Tỷ', 'Tỉ', 'ty', '1') THEN price * 1000000000 "
            . "WHEN price_unit IN ('Triệu', 'trieu', '2') THEN price * 1000000 "
            . "ELSE price END";
    }

    private function normalizeListingPayload(array $data, ?RealEstateListing $listing = null): array
    {
        if (array_key_exists('images', $data) && is_array($data['images'])) {
            $data['images'] = array_values(array_filter($data['images']));
            $data['avatar'] = $data['avatar'] ?? ($data['images'][0] ?? null);
        }

        foreach (['amenities', 'tags'] as $field) {
            if (array_key_exists($field, $data) && is_array($data[$field])) {
                $data[$field] = array_values(array_filter($data[$field]));
            }
        }

        if (! empty($data['title']) && ($listing === null || $listing->title !== $data['title'])) {
            $data['slug'] = $this->makeSlug($data['title'], $listing ? $listing->id : null);
        }

        if (! empty($data['province'])) {
            $data['province_name'] = $data['province'];
            unset($data['province']);
        }
        if (! empty($data['district'])) {
            $data['district_name'] = $data['district'];
            unset($data['district']);
        }
        if (! empty($data['ward'])) {
            $data['ward_name'] = $data['ward'];
            unset($data['ward']);
        }

        return $data;
    }

    private function makeCode($propertyType = null): string
    {
        $prefixes = [
            103 => 'CH',
            104 => 'D',
            107 => 'MP',
            108 => 'NR',
            115 => 'NT',
        ];

        $prefix = $prefixes[(int) $propertyType] ?? 'BDS';

        do {
            $code = $prefix . '-' . strtoupper(Str::random(7));
        } while (RealEstateListing::where('code', $code)->exists());

        return $code;
    }

    private function makeSlug(string $title, ?int $id = null): string
    {
        $base = Str::slug($title) ?: 'tin-dang';
        $suffix = $id ? '-' . $id : '-' . strtolower(Str::random(5));
        $slug = $base . $suffix;

        $exists = RealEstateListing::where('slug', $slug)
            ->when($id, function ($q) use ($id) {
                $q->where('id', '<>', $id);
            })
            ->exists();

        return $exists ? $base . '-' . strtolower(Str::random(8)) : $slug;
    }
}
