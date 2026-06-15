<?php

namespace App\Http\Resources;

use App\Livewire\RealEstateListing as RealEstateListingLivewire;
use App\Models\RealEstateListing;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = $request->user();
        $reveal = $user !== null;
        $propertyTypeCode = is_numeric($this->property_type) ? (int) $this->property_type : null;
        $propertyTypeLabel = $propertyTypeCode
            ? (RealEstateListingLivewire::PROPERTY_TYPES[$propertyTypeCode] ?? (string) $this->property_type)
            : (string) $this->property_type;

        $images = $this->normalizeStringArray($this->images);
        $avatar = $this->avatar ?: ($images[0] ?? null);
        $contactPhone = $this->contact_phone ?: optional($this->user)->phone;
        $contactName = $this->contact_name ?: optional($this->user)->name;
        $userAvatar = $this->userAvatar();

        $data = [
            'id' => $this->id,
            'code' => $this->code,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'transaction_type' => $this->transactionKind((string) $this->type),
            'property_type' => $propertyTypeLabel,
            'property_type_code' => $propertyTypeCode,
            'property_kind' => $this->propertyKind($propertyTypeCode, $propertyTypeLabel),
            'room_type' => $this->room_type,
            'category_id' => $this->category_id,
            'price' => $this->price,
            'price_unit' => $this->price_unit,
            'price_vnd' => $this->priceVnd((float) $this->price, (string) $this->price_unit),
            'price_unit_normalized' => strpos(mb_strtolower((string) $this->price_unit), 'tháng') !== false ? 'month' : 'total',
            'area' => $this->area,
            'address' => $this->address,
            'ward_id' => $this->ward_id,
            'ward_name' => $this->ward_name,
            'district_id' => $this->district_id,
            'district_name' => $this->district_name,
            'province_id' => $this->province_id,
            'province_name' => $this->province_name,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'floors' => $this->floors,
            'bedrooms' => $this->bedrooms,
            'toilets' => $this->toilets,
            'direction' => $this->direction,
            'furnish' => $this->furnish,
            'front_width' => $this->front_width,
            'road_width' => $this->road_width,
            'avatar' => $avatar,
            'images' => $images,
            'amenities' => $this->normalizeStringArray($this->amenities),
            'tags' => $this->normalizeStringArray($this->tags),
            'video_url' => $this->youtube_link_short ?: $this->youtube_link ?: $this->facebook_video_link ?: $this->tiktok_link,
            'vip_tier' => $this->vip_tier ?: 'normal',
            'status' => $this->is_sold ? 'sold' : ($this->status ?: 'active'),
            'is_sold' => (bool) $this->is_sold,
            'view_count' => (int) ($this->view_count ?? 0),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
            'published_at' => optional($this->published_at ?? $this->created_at)->toISOString(),
            'expires_at' => optional($this->expires_at ?? $this->updated_at)->toISOString(),
            'can_view_phone' => $reveal,
            'contact_name' => $contactName,
            'contact_avatar' => $userAvatar,
            'owner' => $this->whenLoaded('user', function () {
                $userAvatar = $this->userAvatar();

                return [
                'id' => optional($this->user)->id,
                'name' => optional($this->user)->name,
                'phone' => optional($this->user)->phone,
                'avatar' => $userAvatar,
                ];
            }),
            'is_favorited' => $user
                ? $this->favorites()->where('user_id', $user->id)->exists()
                : false,
        ];

        if ($reveal) {
            $data['contact_phone'] = $contactPhone;
            $data['contact_phones'] = RealEstateListing::parseContactPhones($contactPhone);
        } else {
            $data['contact_phone'] = $contactPhone ? substr($contactPhone, 0, 3) . '*******' : null;
            $data['contact_phones'] = [];
        }

        return $data;
    }

    private function userAvatar(): ?string
    {
        if (! $this->resource->relationLoaded('user') || ! $this->user) {
            return null;
        }

        return array_key_exists('avatar', $this->user->getAttributes())
            ? $this->user->getAttribute('avatar')
            : null;
    }

    private function normalizeStringArray($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, function ($item) {
            return is_string($item) && $item !== '';
        }));
    }

    private function transactionKind(string $type): string
    {
        return strpos(mb_strtolower($type), 'thuê') !== false ? 'rent' : 'sale';
    }

    private function propertyKind(?int $code, ?string $label): string
    {
        if ($code === 103) {
            return 'apartment';
        }
        if (in_array($code, [104, 105, 109], true)) {
            return 'land';
        }
        if (in_array($code, [106, 107, 111, 112, 113], true)) {
            return 'office';
        }
        if ($code === 115) {
            return 'room';
        }

        return $this->propertyKindFromLabel((string) $label);
    }

    private function propertyKindFromLabel(string $label): string
    {
        $value = mb_strtolower($label);
        if (strpos($value, 'căn hộ') !== false || strpos($value, 'chung cư') !== false) {
            return 'apartment';
        }
        if (strpos($value, 'trọ') !== false) {
            return 'room';
        }
        if (strpos($value, 'đất') !== false || strpos($value, 'trang trại') !== false) {
            return 'land';
        }
        if (strpos($value, 'mặt') !== false || strpos($value, 'văn phòng') !== false || strpos($value, 'khách sạn') !== false) {
            return 'office';
        }

        return 'house';
    }

    private function priceVnd(float $price, string $unit): float
    {
        if ($price >= 1000000) {
            return $price;
        }

        if (in_array($unit, ['Tỷ', 'Tỉ', 'ty', '1'], true)) {
            return $price * 1000000000;
        }
        if (in_array($unit, ['Triệu', 'trieu', '2'], true)) {
            return $price * 1000000;
        }

        return $price;
    }
}
