<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateExtensionConfigRequest;
use App\Models\ExtensionSetting;
use App\Services\ExtensionConfigSigner;
use Illuminate\Http\JsonResponse;

class ExtensionConfigController extends Controller
{
    public function show(ExtensionConfigSigner $signer): JsonResponse
    {
        $setting = ExtensionSetting::current();
        $issuedAt = now();
        $payload = array_merge($setting->value, [
            'revision' => $setting->updated_at?->getTimestamp() ?? 0,
            'issuedAt' => $issuedAt->getTimestamp(),
            'expiresAt' => $issuedAt->copy()->addSeconds(config('extension.signature_ttl_seconds'))->getTimestamp(),
        ]);

        return response()->json([
            'data' => $payload,
            'proof' => $signer->sign($payload, $setting),
        ]);
    }

    public function update(UpdateExtensionConfigRequest $request, ExtensionConfigSigner $signer): JsonResponse
    {
        $setting = ExtensionSetting::current();
        $setting->forceFill([
            'value' => $request->validated(),
            'updated_by' => $request->user()->id,
        ])->save();

        return $this->show($signer);
    }
}
