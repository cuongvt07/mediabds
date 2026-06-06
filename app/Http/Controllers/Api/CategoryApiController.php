<?php

namespace App\Http\Controllers\Api;

use App\Models\ListingCategory;

class CategoryApiController extends BaseApiController
{
    public function index()
    {
        $categories = ListingCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (ListingCategory $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'transactionType' => $category->transaction_type,
                    'propertyType' => $category->property_type,
                    'icon' => $category->icon,
                ];
            });

        return $this->ok($categories);
    }
}
