<?php

namespace App\Http\Controllers\Vault;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Base controller riêng cho module Vault — cố tình KHÔNG kế thừa
 * App\Http\Controllers\Api\BaseApiController để module này độc lập hoàn toàn,
 * có thể tách thành package riêng sau này mà không kéo theo code của site chính.
 */
abstract class VaultBaseController extends Controller
{
    protected function ok($data = null, ?string $message = null, int $code = 200): JsonResponse
    {
        $payload = ['success' => true];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        if ($message !== null) {
            $payload['message'] = $message;
        }

        return response()->json($payload, $code);
    }

    protected function fail(string $message, int $code = 400, array $extra = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($extra)) {
            $payload['errors'] = $extra;
        }

        return response()->json($payload, $code);
    }
}
