<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\LeadRequest;
use App\Models\Customer;
use App\Models\CustomerWork;
use Illuminate\Http\JsonResponse;

class LeadApiController extends BaseApiController
{
    /**
     * Store a new lead coming from the public Next.js website.
     *
     * Creates a Customer record (status = "mua", chưa phân công) and
     * an initial CustomerWork entry so the lead appears on the timeline.
     */
    public function store(LeadRequest $req): JsonResponse
    {
        $data = $req->validated();

        $description = 'Lead từ website Next.js'
            .(isset($data['listing_id']) ? ' (quan tâm listing #'.$data['listing_id'].')' : '');

        $customer = Customer::create([
            'code' => Customer::generateCode(),
            'name' => $data['name'],
            'phone' => $data['phone'],
            'status' => 'mua',
            'assigned_user_id' => null, // admin sẽ phân công sau
            'budget_from' => $data['budget_from'] ?? null,
            'budget_to' => $data['budget_to'] ?? null,
            'description' => $description,
        ]);

        // Ghi CustomerWork để timeline:
        CustomerWork::create([
            'customer_id' => $customer->id,
            'user_id' => null,
            'work_date' => now(),
            'content' => 'Yêu cầu tư vấn từ website. '.($data['message'] ?? ''),
            'progress' => 'Mới',
        ]);

        return $this->ok(
            ['lead_id' => $customer->id, 'code' => $customer->code],
            'Đã ghi nhận, chúng tôi sẽ liên hệ sớm',
            201
        );
    }
}
