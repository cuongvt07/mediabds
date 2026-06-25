<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CreateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\VehicleListing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VehicleApiController extends BaseApiController
{
    public function index(Request $req)
    {
        $perPage = min((int) $req->integer('per_page', 12), 30);
        if ($perPage < 1) {
            $perPage = 12;
        }

        $query = VehicleListing::query()
            ->with('user:id,name,phone')
            ->where('is_sold', false)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            });

        if ($req->filled('q')) {
            $term = trim((string) $req->string('q'));
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%")
                    ->orWhere('model_name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%");
            });
        }

        if ($req->filled('vehicle_type')) {
            $query->where('vehicle_type', $req->string('vehicle_type'));
        }
        if ($req->filled('type')) {
            $query->where('type', $req->string('type'));
        }
        if ($req->filled('brand')) {
            $query->where('brand', $req->string('brand'));
        }
        if ($req->filled('transmission')) {
            $query->where('transmission', $req->string('transmission'));
        }
        if ($req->filled('fuel_type')) {
            $query->where('fuel_type', $req->string('fuel_type'));
        }
        if ($req->filled('condition')) {
            $query->where('condition', $req->string('condition'));
        }
        if ($req->filled('user_id')) {
            $query->where('user_id', (int) $req->integer('user_id'));
        }

        foreach (['province', 'district', 'ward'] as $loc) {
            if ($req->filled($loc)) {
                $v = (string) $req->string($loc);
                $query->where(function ($q) use ($loc, $v) {
                    $q->where("{$loc}_id", $v)->orWhere("{$loc}_name", 'like', "%{$v}%");
                });
            }
        }

        if ($req->filled('min_year')) {
            $query->where('year', '>=', (int) $req->integer('min_year'));
        }
        if ($req->filled('max_year')) {
            $query->where('year', '<=', (int) $req->integer('max_year'));
        }
        if ($req->filled('max_mileage')) {
            $query->where('mileage', '<=', (int) $req->integer('max_mileage'));
        }
        if ($req->filled('seats')) {
            $query->where('seats', (int) $req->integer('seats'));
        }

        if ($req->boolean('vip_only')) {
            $query->where('vip_tier', '<>', 'normal');
        }

        if ($req->filled('min_price') || $req->filled('max_price')) {
            $priceExpr = $this->priceVndExpression();
            if ($req->filled('min_price')) {
                $query->whereRaw("({$priceExpr}) >= ?", [(float) $req->input('min_price') * 1000000000]);
            }
            if ($req->filled('max_price')) {
                $query->whereRaw("({$priceExpr}) <= ?", [(float) $req->input('max_price') * 1000000000]);
            }
        }

        $sortBy = $req->input('sort_by', 'created_at');
        $sortOrder = strtolower((string) $req->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (! in_array($sortBy, ['created_at', 'price', 'year', 'mileage', 'view_count'], true)) {
            $sortBy = 'created_at';
        }

        if ($sortBy === 'price') {
            $query->orderByRaw('(' . $this->priceVndExpression() . ') ' . $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        return VehicleResource::collection($query->paginate($perPage)->appends($req->query()));
    }

    public function show($idOrCode)
    {
        $listing = VehicleListing::with('user:id,name,phone')
            ->where(function ($q) use ($idOrCode) {
                if (is_numeric($idOrCode)) {
                    $q->where('id', (int) $idOrCode);
                }
                $q->orWhere('code', $idOrCode)->orWhere('slug', $idOrCode);
                if (preg_match('/-(\d+)$/', (string) $idOrCode, $m)) {
                    $q->orWhere('id', (int) $m[1]);
                }
            })
            ->first();

        if (! $listing) {
            return $this->fail('Không tìm thấy tin', 404);
        }

        $listing->increment('view_count');

        return new VehicleResource($listing);
    }

    public function store(CreateVehicleRequest $req)
    {
        $data = $this->normalizePayload($req->validated());
        $data['user_id'] = auth()->id();
        $data['code'] = $this->makeCode($data['vehicle_type'] ?? 'car');
        $data['type'] = $data['type'] ?? 'Cần bán';
        $data['status'] = $data['status'] ?? 'active';
        $data['published_at'] = now();
        $data['expires_at'] = now()->addDays(60);

        $vehicle = VehicleListing::create($data);
        $vehicle->slug = $this->makeSlug($vehicle->title, $vehicle->id);
        $vehicle->save();

        return $this->ok(new VehicleResource($vehicle->fresh('user')), 'Created', 201);
    }

    public function update(CreateVehicleRequest $req, $id)
    {
        $vehicle = VehicleListing::findOrFail($id);
        $this->authorizeOwner($vehicle);

        $vehicle->update($this->normalizePayload($req->validated(), $vehicle));

        return $this->ok(new VehicleResource($vehicle->fresh('user')));
    }

    public function destroy($id)
    {
        $vehicle = VehicleListing::findOrFail($id);
        $this->authorizeOwner($vehicle);

        $vehicle->delete();

        return $this->ok(null, 'Deleted');
    }

    private function authorizeOwner(VehicleListing $vehicle): void
    {
        $user = auth()->user();
        if (! $user || ($vehicle->user_id !== $user->id && ! $user->isAdmin())) {
            abort(403, 'Không có quyền thao tác tin này');
        }
    }

    private function normalizePayload(array $data, ?VehicleListing $vehicle = null): array
    {
        if (array_key_exists('images', $data) && is_array($data['images'])) {
            $data['images'] = array_values(array_filter($data['images']));
            $data['avatar'] = $data['avatar'] ?? ($data['images'][0] ?? null);
        }
        if (array_key_exists('tags', $data) && is_array($data['tags'])) {
            $data['tags'] = array_values(array_filter($data['tags']));
        }

        // Cho phép gửi province/district (tên) thay cho *_name.
        if (! empty($data['province'])) {
            $data['province_name'] = $data['province'];
        }
        if (! empty($data['district'])) {
            $data['district_name'] = $data['district'];
        }
        unset($data['province'], $data['district']);

        if (! empty($data['title']) && ($vehicle === null || $vehicle->title !== $data['title'])) {
            $data['slug'] = $this->makeSlug($data['title'], $vehicle?->id);
        }

        return $data;
    }

    private function makeCode(string $vehicleType): string
    {
        $prefix = $vehicleType === 'motorbike' ? 'XM' : 'OT';
        do {
            $code = $prefix . '-' . strtoupper(Str::random(7));
        } while (VehicleListing::where('code', $code)->exists());

        return $code;
    }

    private function makeSlug(string $title, ?int $id = null): string
    {
        $base = Str::slug($title) ?: 'tin-xe';
        $suffix = $id ? '-' . $id : '-' . strtolower(Str::random(5));
        $slug = $base . $suffix;

        $exists = VehicleListing::where('slug', $slug)
            ->when($id, fn ($q) => $q->where('id', '<>', $id))
            ->exists();

        return $exists ? $base . '-' . strtolower(Str::random(8)) : $slug;
    }

    private function priceVndExpression(): string
    {
        return "CASE "
            . "WHEN price >= 1000000 THEN price "
            . "WHEN price_unit IN ('Tỷ', 'Tỉ', 'ty', '1') THEN price * 1000000000 "
            . "WHEN price_unit IN ('Triệu', 'trieu', '2') THEN price * 1000000 "
            . "ELSE price END";
    }
}
