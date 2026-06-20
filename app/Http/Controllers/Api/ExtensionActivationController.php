<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ActivateExtensionRequest;
use App\Models\ExtensionDevice;
use App\Models\ExtensionLicense;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExtensionActivationController extends Controller
{
    public function store(ActivateExtensionRequest $request): JsonResponse
    {
        $input = $request->validated();
        $license = ExtensionLicense::query()->where('key_hash', hash('sha256', $input['licenseKey']))->first();

        if (!$license || !$license->isUsable()) {
            return response()->json(['message' => 'License không hợp lệ hoặc đã hết hạn.'], 403);
        }

        $deviceHash = hash('sha256', $input['deviceId']);
        $result = DB::transaction(function () use ($license, $deviceHash, $input) {
            $device = $license->devices()->where('device_hash', $deviceHash)->lockForUpdate()->first();
            if (!$device) {
                $activeDevices = $license->devices()->whereNull('revoked_at')->lockForUpdate()->count();
                if ($activeDevices >= $license->max_devices) return null;
                $device = $license->devices()->create([
                    'device_hash' => $deviceHash,
                    'device_name' => $input['deviceName'] ?? null,
                ]);
            }

            if ($device->revoked_at) return null;

            $plainToken = 'ext_'.Str::random(64);
            $device->forceFill([
                'device_name' => $input['deviceName'] ?? $device->device_name,
                'token_hash' => hash('sha256', $plainToken),
                'last_seen_at' => now(),
            ])->save();

            return [$device, $plainToken];
        });

        if (!$result) {
            return response()->json(['message' => 'License đã đủ thiết bị hoặc thiết bị đã bị thu hồi.'], 409);
        }

        [$device, $plainToken] = $result;
        return response()->json([
            'deviceToken' => $plainToken,
            'license' => [
                'label' => $license->label,
                'expiresAt' => $license->expires_at?->toIso8601String(),
                'deviceId' => $device->id,
            ],
        ]);
    }
}
