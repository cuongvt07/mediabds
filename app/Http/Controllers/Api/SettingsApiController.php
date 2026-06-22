<?php

namespace App\Http\Controllers\Api;

use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SettingsApiController extends BaseApiController
{
    /**
     * Public site settings consumed by the Next.js frontend.
     * Only safe, non-secret keys are exposed.
     */
    public function show(): JsonResponse
    {
        $values = SiteSetting::values();

        return $this->ok([
            'contact' => $values['contact'] ?? [],
            'packages' => $values['packages'] ?? [],
            'upload' => $values['upload'] ?? [],
            'watermark' => [
                'enabled' => (bool) ($values['watermark']['enabled'] ?? false),
            ],
        ]);
    }
}
