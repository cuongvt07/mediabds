<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateListingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isCtv() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'required|in:Cần bán,Cho thuê,Cần mua',
            'property_type' => 'required|integer',
            'price' => 'required|numeric|min:0',
            'price_unit' => 'required|in:Tỷ,Triệu,VNĐ/tháng,Thỏa thuận',
            'area' => 'required|numeric|min:0',
            'contact_phone' => 'required|string',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'bedrooms' => 'nullable|integer',
            'toilets' => 'nullable|integer',
            'floors' => 'nullable|integer',
            'direction' => 'nullable|string',
            'front_width' => 'nullable|numeric',
            'road_width' => 'nullable|numeric',
        ];
    }
}
