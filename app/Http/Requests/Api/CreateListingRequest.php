<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|min:10|max:255',
            'type' => 'required|string|max:50',
            'property_type' => 'required',
            'category_id' => 'nullable|string|max:80',
            'status' => 'nullable|in:active,pending,expired,sold',
            'vip_tier' => 'nullable|in:normal,vip1,vip2,vip3',

            'price' => 'required|numeric|min:0',
            'price_unit' => 'required|string|max:50',
            'area' => 'required|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'toilets' => 'nullable|integer|min:0',
            'floors' => 'nullable|integer|min:0',
            'direction' => 'nullable|string|max:50',
            'furnish' => 'nullable|string|max:30',
            'front_width' => 'nullable|numeric|min:0',
            'road_width' => 'nullable|numeric|min:0',

            'province_id' => 'nullable|string|max:50',
            'district_id' => 'nullable|string|max:50',
            'ward_id' => 'nullable|string|max:50',
            'province_name' => 'nullable|string|max:120',
            'district_name' => 'nullable|string|max:120',
            'ward_name' => 'nullable|string|max:120',
            'province' => 'nullable|string|max:120',
            'district' => 'nullable|string|max:120',
            'ward' => 'nullable|string|max:120',
            'address' => 'nullable|string|max:500',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',

            'contact_phone' => 'required|string|max:50',
            'contact_name' => 'nullable|string|max:120',
            'contact_zalo' => 'nullable|string|max:50',
            'contact_type' => 'nullable|string|max:50',
            'description' => 'nullable|string',

            'avatar' => 'nullable|string|max:2048',
            'images' => 'nullable|array|max:30',
            'images.*' => 'string|max:2048',
            'amenities' => 'nullable|array|max:50',
            'amenities.*' => 'string|max:80',
            'tags' => 'nullable|array|max:30',
            'tags.*' => 'string|max:80',

            'youtube_link' => 'nullable|string|max:2048',
            'youtube_link_short' => 'nullable|string|max:2048',
            'facebook_video_link' => 'nullable|string|max:2048',
            'google_map_link' => 'nullable|string|max:2048',
            'tiktok_link' => 'nullable|string|max:2048',
        ];
    }
}
