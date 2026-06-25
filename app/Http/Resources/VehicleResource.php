<?php

namespace App\Http\Resources;

use App\Models\VehicleListing;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = $request->user();
        $reveal = $user !== null;

        $images = $this->normalizeStringArray($this->images);
        $avatar = $this->avatar ?: ($images[0] ?? null);
        $contactPhone = $this->contact_phone ?: optional($this->user)->phone;
        $contactName = $this->contact_name ?: optional($this->user)->name;

        $data = [
            'id' => $this->id,
            'code' => $this->code,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'transaction_type' => $this->transactionKind((string) $this->type),

            // Phân loại xe
            'vehicle_type' => $this->vehicle_type,
            'vehicle_type_label' => VehicleListing::VEHICLE_TYPES[$this->vehicle_type] ?? (string) $this->vehicle_type,

            // Thông số xe
            'brand' => $this->brand,
            'model_name' => $this->model_name,
            'year' => $this->year !== null ? (int) $this->year : null,
            'mileage' => $this->mileage !== null ? (int) $this->mileage : null,
            'transmission' => $this->transmission,
            'transmission_label' => VehicleListing::TRANSMISSIONS[$this->transmission] ?? $this->transmission,
            'fuel_type' => $this->fuel_type,
            'fuel_type_label' => VehicleListing::FUEL_TYPES[$this->fuel_type] ?? $this->fuel_type,
            'engine_capacity' => $this->engine_capacity,
            'color' => $this->color,
            'seats' => $this->seats !== null ? (int) $this->seats : null,
            'condition' => $this->condition,
            'condition_label' => VehicleListing::CONDITIONS[$this->condition] ?? $this->condition,
            'origin' => $this->origin,
            'origin_label' => VehicleListing::ORIGINS[$this->origin] ?? $this->origin,

            // Giá
            'price' => $this->price,
            'price_unit' => $this->price_unit,
            'price_vnd' => $this->priceVnd((float) $this->price, (string) $this->price_unit),

            // Vị trí
            'address' => $this->address,
            'ward_id' => $this->ward_id,
            'ward_name' => $this->ward_name,
            'district_id' => $this->district_id,
            'district_name' => $this->district_name,
            'province_id' => $this->province_id,
            'province_name' => $this->province_name,
            'lat' => $this->lat,
            'lng' => $this->lng,

            // Media
            'avatar' => $avatar,
            'images' => $images,
            'tags' => $this->normalizeStringArray($this->tags),
            'video_url' => $this->youtube_link,

            // Trạng thái
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
            'owner' => $this->whenLoaded('user', fn () => [
                'id' => optional($this->user)->id,
                'name' => optional($this->user)->name,
                'phone' => optional($this->user)->phone,
            ]),
        ];

        $data['contact_phone'] = $reveal
            ? $contactPhone
            : ($contactPhone ? substr($contactPhone, 0, 3) . '*******' : null);

        return $data;
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

        return array_values(array_filter($value, fn ($item) => is_string($item) && $item !== ''));
    }

    private function transactionKind(string $type): string
    {
        return strpos(mb_strtolower($type), 'thuê') !== false ? 'rent' : 'sale';
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
