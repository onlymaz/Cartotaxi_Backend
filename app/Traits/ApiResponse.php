<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;

/**
 * Provides consistent JSON response helpers for API controllers.
 * Use this trait in any controller that serves API endpoints.
 */
trait ApiResponse
{
    /**
     * Return a standardised validation-error JSON response.
     * Uses HTTP 422 Unprocessable Entity as per RFC 9110.
     */
    protected function validationErrorResponse(Validator $validator, int $status = 422): JsonResponse
    {
        $messages = implode(' ', array_column($validator->messages()->getMessages(), 0));

        return response()->json([
            'status'   => false,
            'messages' => $messages,
        ], $status);
    }

    /**
     * Run validation and immediately return an error response if it fails.
     * Returns null when validation passes.
     *
     * Usage:
     *   if ($response = $this->validateRequest($request->all(), $rules)) {
     *       return $response;
     *   }
     */
    protected function validateRequest(array $data, array $rules, array $messages = []): ?JsonResponse
    {
        $validator = $this->getValidationFactory()->make($data, $rules, $messages);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        return null;
    }

    /**
     * Standard success response.
     */
    protected function successResponse(string $message, array $data = [], int $status = 200): JsonResponse
    {
        $payload = ['status' => true, 'messages' => $message];
        if (!empty($data)) {
            $payload['data'] = $data;
        }
        return response()->json($payload, $status);
    }

    /**
     * Standard error response.
     */
    protected function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'status'   => false,
            'messages' => $message,
        ], $status);
    }
}
