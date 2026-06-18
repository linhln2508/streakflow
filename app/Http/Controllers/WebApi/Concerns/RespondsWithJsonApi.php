<?php

namespace App\Http\Controllers\WebApi\Concerns;

use Illuminate\Http\JsonResponse;

trait RespondsWithJsonApi
{
    protected function jsonSuccess(mixed $data = [], ?string $message = null, int $status = 200): JsonResponse
    {
        $payload = ['success' => true, 'data' => $data];

        if ($message !== null && $message !== '') {
            $payload['message'] = $message;
        }

        return response()->json($payload, $status);
    }

    protected function jsonFail(string $message, int $status = 422, mixed $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function jsonValidationError($validator): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => __('validation.failed'),
            'errors' => $validator->errors(),
            'data' => null,
        ], 422);
    }
}
