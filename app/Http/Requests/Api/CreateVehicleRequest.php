<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|min:5|max:255',
            'type' => 'nullable|string|max:50',
            'vehicle_type' => 'required|in:car,motorbike',

            'brand' => 'nullable|string|max:80',
            'model_name' => 'nullable|string|max:120',
            'year' => 'nullable|integer|min:1950|max:2100',
            'mileage' => 'nullable|integer|min:0',
            'transmission' => 'nullable|string|max:40',
            'fuel_type' => 'nullable|string|max:40',
            'engine_capacity' => 'nullable|string|max:40',
            'color' => 'nullable|string|max:40',
            'seats' => 'nullable|integer|min:1|max:64',
            'condition' => 'nullable|in:new,used',
            'origin' => 'nullable|string|max:40',

            'price' => 'nullable|numeric|min:0',
            'price_unit' => 'nullable|string|max:30',

            'province_name' => 'nullable|string|max:120',
            'district_name' => 'nullable|string|max:120',
            'province' => 'nullable|string|max:120',
            'district' => 'nullable|string|max:120',
            'address' => 'nullable|string|max:500',

            'contact_name' => 'nullable|string|max:120',
            'contact_phone' => 'required|string|max:50',
            'contact_zalo' => 'nullable|string|max:50',

            'description' => 'nullable|string',
            'avatar' => 'nullable|string|max:2048',
            'images' => 'nullable|array|max:30',
            'images.*' => 'string|max:2048',
            'tags' => 'nullable|array|max:30',
            'tags.*' => 'string|max:80',
            'youtube_link' => 'nullable|string|max:2048',

            'status' => 'nullable|in:active,pending,expired,sold',
            'vip_tier' => 'nullable|in:normal,vip1,vip2,vip3',
        ];
    }
}
