<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\LeadRequest;
use App\Models\Customer;
use App\Models\CustomerWork;
use App\Models\ListingContactRequest;
use Illuminate\Http\JsonResponse;

class LeadApiController extends BaseApiController
{
    public function store(LeadRequest $req): JsonResponse
    {
        $data = $req->validated();

        $description = 'Lead từ website'
            . (isset($data['listing_id']) ? ' (quan tâm listing #' . $data['listing_id'] . ')' : '');

        $customer = Customer::create([
            'code' => Customer::generateCode(),
            'name' => $data['name'],
            'phone' => $data['phone'],
            'status' => 'mua',
            'assigned_user_id' => null,
            'budget_from' => $data['budget_from'] ?? null,
            'budget_to' => $data['budget_to'] ?? null,
            'description' => $description,
        ]);

        CustomerWork::create([
            'customer_id' => $customer->id,
            'user_id' => null,
            'work_date' => now(),
            'content' => 'Yêu cầu tư vấn từ website. ' . ($data['message'] ?? ''),
            'progress' => 'Mới',
        ]);

        ListingContactRequest::create([
            'listing_id' => $data['listing_id'] ?? null,
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'phone' => $data['phone'],
            'message' => $data['message'] ?? null,
            'source' => 'website',
        ]);

        return $this->ok(
            ['lead_id' => $customer->id, 'code' => $customer->code],
            'Đã ghi nhận, chúng tôi sẽ liên hệ sớm',
            201
        );
    }
}
