<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    /**
     * Build a successful JSON response.
     *
     * @param  mixed  $data
     */
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

    /**
     * Build a failed JSON response.
     *
     * @param  array<string, mixed>  $extra
     */
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
