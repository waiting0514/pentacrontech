<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(mixed $data = null, ?string $message = 'Operation successful', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(string $message, string $errorCode, int $httpCode = 400, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'code' => $httpCode,
            'error_code' => $errorCode,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $httpCode);
    }
}
