<?php

namespace App\Http\Controllers\Api;

use App\Models\ListingReport;
use App\Models\RealEstateListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportApiController extends BaseApiController
{
    /**
     * Submit a report against a listing or a user account. Public — guests may
     * report and supply contact details; logged-in users are attributed automatically.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'target_type' => ['required', Rule::in(['listing', 'user'])],
            'listing_id' => ['nullable', 'required_if:target_type,listing', 'integer', 'exists:real_estate_listings,id'],
            'reported_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'reason' => ['required', Rule::in(array_keys(ListingReport::REASONS))],
            'detail' => ['nullable', 'string', 'max:2000'],
            'reporter_name' => ['nullable', 'string', 'max:160'],
            'reporter_phone' => ['nullable', 'string', 'max:40'],
        ]);

        // For a listing report, default the reported user to the listing owner.
        $reportedUserId = $data['reported_user_id'] ?? null;
        if ($data['target_type'] === 'listing' && ! $reportedUserId) {
            $listing = RealEstateListing::query()->find($data['listing_id']);
            $reportedUserId = $listing?->user_id ?? $listing?->reporter_id;
        }

        $report = ListingReport::create([
            'target_type' => $data['target_type'],
            'listing_id' => $data['listing_id'] ?? null,
            'reported_user_id' => $reportedUserId,
            'reporter_user_id' => $request->user()?->id,
            'reporter_name' => $data['reporter_name'] ?? $request->user()?->name,
            'reporter_phone' => $data['reporter_phone'] ?? $request->user()?->phone,
            'reason' => $data['reason'],
            'detail' => $data['detail'] ?? null,
            'status' => 'pending',
        ]);

        return $this->ok(['id' => $report->id], 'Đã gửi báo cáo. Cảm ơn bạn!', 201);
    }
}
