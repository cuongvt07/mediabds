<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ListingResource;
use App\Http\Resources\VehicleResource;
use App\Models\BlogPost;
use App\Models\RealEstateListing;
use App\Models\VehicleListing;
use App\Models\WebsiteHomeSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class HomepageApiController extends BaseApiController
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('website_home_sections')) {
            return $this->ok([]);
        }

        $sections = WebsiteHomeSection::query()
            ->where('enabled', true)
            ->orderBy('sort_order_index')
            ->orderBy('id')
            ->get()
            ->map(function (WebsiteHomeSection $section) use ($request) {
                return $this->sectionPayload($section, $request);
            })
            ->values();

        return $this->ok($sections);
    }

    private function sectionPayload(WebsiteHomeSection $section, Request $request): array
    {
        $payload = [
            'key' => $section->key,
            'title' => $section->title,
            'description' => $section->description,
            'section_type' => $section->section_type,
            'source_type' => $section->source_type,
            'href' => $section->href,
            'limit' => (int) $section->limit,
            'sort_order_index' => (int) $section->sort_order_index,
            'config' => $section->config ?: new \stdClass(),
            'meta' => ['total' => 0],
            'items' => [],
        ];

        if ($section->section_type === 'listings') {
            $query = $this->listingQuery($section);
            $total = (clone $query)->count();
            $items = $query->limit(max(1, min((int) $section->limit, 24)))->get();

            $payload['meta']['total'] = $total;
            $payload['items'] = ListingResource::collection($items)->resolve($request);
        }

        if ($section->section_type === 'vehicles' && Schema::hasTable('vehicle_listings')) {
            $query = $this->vehicleQuery($section);
            $total = (clone $query)->count();
            $items = $query->limit(max(1, min((int) $section->limit, 24)))->get();

            $payload['meta']['total'] = $total;
            $payload['items'] = VehicleResource::collection($items)->resolve($request);
        }

        if ($section->section_type === 'blogs' && Schema::hasTable('blog_posts')) {
            $query = BlogPost::query()
                ->where('status', 'published')
                ->orderByDesc('published_at')
                ->orderByDesc('created_at');

            $payload['meta']['total'] = (clone $query)->count();
            $payload['items'] = $query->limit(max(1, min((int) $section->limit, 20)))->get()->toArray();
        }

        return $payload;
    }

    private function listingQuery(WebsiteHomeSection $section)
    {
        $query = RealEstateListing::query()
            ->with('user:id,name,phone')
            ->where('is_sold', false)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            });

        if ($section->source_type === 'manual') {
            $ids = collect($section->manual_listing_ids ?: [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values()
                ->all();

            if (! empty($ids)) {
                $query->whereIn('id', $ids)
                    ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')');
                return $query;
            }
        }

        if ($section->source_type === 'vip') {
            $query->where('vip_tier', '<>', 'normal');
        }

        if ($section->source_type === 'property' && $section->property_kind) {
            $codes = $this->propertyCodes($section->property_kind);
            if (! empty($codes)) {
                $query->whereIn('property_type', $codes);
            }
        }

        if ($section->source_type === 'category' && $section->category_id) {
            $query->where('category_id', $section->category_id);
        }

        if ($section->source_type === 'province' && $section->province_name) {
            $province = $section->province_name;
            $query->where(function ($q) use ($province) {
                $q->where('province_id', $province)
                    ->orWhere('province_name', 'like', '%' . $province . '%');
            });
        }

        if ($section->transaction_type === 'sale') {
            $query->where(function ($q) {
                $q->where('type', 'like', '%ban%')
                    ->orWhere('type', 'like', '%bán%')
                    ->orWhere('type', 'like', '%bán%')
                    ->orWhere('type', 'like', '%Cần bán%');
            });
        } elseif ($section->transaction_type === 'rent') {
            $query->where(function ($q) {
                $q->where('type', 'like', '%thuê%')
                    ->orWhere('type', 'like', '%thue%')
                    ->orWhere('type', 'like', '%Cho thuê%');
            });
        }

        $sortBy = in_array($section->sort_by, ['created_at', 'price', 'area', 'view_count'], true)
            ? $section->sort_by
            : 'created_at';
        $sortOrder = strtolower((string) $section->sort_order) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortOrder);
    }

    private function vehicleQuery(WebsiteHomeSection $section)
    {
        $query = VehicleListing::query()
            ->with('user:id,name,phone')
            ->where('is_sold', false)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            });

        // property_kind được tái dùng để lọc loại xe: car | motorbike (rỗng = tất cả).
        if (in_array($section->property_kind, ['car', 'motorbike'], true)) {
            $query->where('vehicle_type', $section->property_kind);
        }

        if ($section->source_type === 'vip') {
            $query->where('vip_tier', '<>', 'normal');
        }

        $sortBy = in_array($section->sort_by, ['created_at', 'price', 'year', 'mileage', 'view_count'], true)
            ? $section->sort_by
            : 'created_at';
        $sortOrder = strtolower((string) $section->sort_order) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortOrder);
    }

    private function propertyCodes(?string $kind): array
    {
        return match ($kind) {
            'apartment' => [103],
            'room' => [115],
            'land' => [104, 105, 109],
            'office' => [106, 107, 111, 112, 113],
            'house' => [102, 108, 114],
            default => [],
        };
    }
}
