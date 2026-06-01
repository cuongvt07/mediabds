<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Note: password, view_phone_pin and remember_token are intentionally
     * excluded and must never be exposed via the API.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $rank = $this->rank;

        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'role'              => $this->role,
            'avatar'            => $this->avatar,
            'invite_code'       => $this->invite_code,
            'property_types'    => $this->property_types,
            'rank'              => $rank ? [
                'id'          => $rank->id,
                'name'        => $rank->name,
                'min_price'   => $rank->min_price,
                'min_invites' => $rank->min_invites,
            ] : null,
            'total_revenue'     => $this->total_revenue,
            'invites_count'     => $this->invitees()->count(),
            'trial_ends_at'     => $this->trial_ends_at,
            'license_expires_at' => $this->license_expires_at,
            'is_admin'          => $this->isAdmin(),
            'created_at'        => $this->created_at,
        ];
    }
}
