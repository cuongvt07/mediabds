<?php

namespace App\Http\Middleware;

use App\Models\ExtensionDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateExtensionDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!$token || !str_starts_with($token, 'ext_')) {
            return response()->json(['message' => 'Thiếu device token.'], 401);
        }

        $device = ExtensionDevice::query()
            ->with('license')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (!$device || !$device->isUsable()) {
            return response()->json(['message' => 'Device token không hợp lệ hoặc đã bị thu hồi.'], 401);
        }

        $device->forceFill(['last_seen_at' => now()])->save();
        $request->attributes->set('extensionDevice', $device);

        return $next($request);
    }
}
