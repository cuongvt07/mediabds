<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LeadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'phone' => 'required|string|min:9|max:15',
            'budget_from' => 'nullable|numeric|min:0',
            'budget_to' => 'nullable|numeric|min:0',
            'message' => 'nullable|string|max:1000',
            'listing_id' => 'nullable|integer|exists:real_estate_listings,id',
        ];
    }
}
