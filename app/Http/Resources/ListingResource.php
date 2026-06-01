<?php

namespace App\Http\Resources;

use App\Livewire\RealEstateListing as RealEstateListingLivewire;
use App\Models\RealEstateListing;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user   = $request->user();
        $reveal = $user !== null;

        // Build text label for property_type from PROPERTY_TYPES constant (if any)
        $propertyTypeLabel = null;
        if (isset($this->property_type)) {
            $propertyTypeLabel = RealEstateListingLivewire::PROPERTY_TYPES[$this->property_type] ?? null;
        }

        // Normalize images to array of full URLs
        $images = $this->images;
        if (is_string($images)) {
            $decoded = json_decode($images, true);
            $images  = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($images)) {
            $images = [];
        }
        $images = array_values(array_filter($images, fn ($i) => is_string($i) && $i !== ''));

        // Avatar full URL (stored as a URL already in this project)
        $avatar = $this->avatar ?: null;

        $data = [
            'id'             => $this->id,
            'code'           => $this->code,
            'title'          => $this->title,
            'type'           => $this->type,
            'property_type'  => $propertyTypeLabel,
            'price'          => $this->price,
            'price_unit'     => $this->price_unit,
            'area'           => $this->area,
            'address'        => $this->address,
            'ward_name'      => $this->ward_name,
            'district_name'  => $this->district_name,
            'province_name'  => $this->province_name,
            'floors'         => $this->floors,
            'bedrooms'       => $this->bedrooms,
            'toilets'        => $this->toilets,
            'direction'      => $this->direction,
            'front_width'    => $this->front_width,
            'road_width'     => $this->road_width,
            'avatar'         => $avatar,
            'images'         => $images,
            'is_sold'        => (bool) $this->is_sold,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'can_view_phone' => $reveal,
        ];

        if ($reveal) {
            $data['contact_phone']   = $this->contact_phone;
            $data['contact_phones']  = RealEstateListing::parseContactPhones($this->contact_phone);
        } else {
            $data['contact_phone']   = $this->contact_phone
                ? substr($this->contact_phone, 0, 3) . '*******'
                : null;
            $data['contact_phones']  = [];
        }

        return $data;
    }
}
