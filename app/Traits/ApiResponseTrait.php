<?php

namespace App\Traits;

trait ApiResponseTrait
{
    private function formatResponse($data, $message, $code)
    {
        if (is_string($data)) {
            $message = $data;
            $data    = [];
        }

        return response()->json([
            'message' => $message,
            'data'    => $data,
            'code'    => $code,
        ], $code);
    }

    public function successResponse($data = [], $message = 'Success')
    {
        return $this->formatResponse($data, $message, 200);
    }

    public function createdResponse($data = [], $message = 'Resource created successfully')
    {
        return $this->formatResponse($data, $message, 201);
    }

    public function badRequestResponse($data = [], $message = 'Bad request')
    {
        return $this->formatResponse($data, $message, 400);
    }

    public function unauthorizedResponse($data = [], $message = 'Unauthorized')
    {
        return $this->formatResponse($data, $message, 401);
    }

    public function forbiddenResponse($data = [], $message = 'Forbidden')
    {
        return $this->formatResponse($data, $message, 403);
    }

    public function notFoundResponse($data = [], $message = 'Resource not found')
    {
        return $this->formatResponse($data, $message, 404);
    }

    public function methodNotAllowedResponse($data = [], $message = 'Method not allowed')
    {
        return $this->formatResponse($data, $message, 405);
    }

    public function validationFailedResponse($data = [], $message = 'Validation failed')
    {
        return $this->formatResponse($data, $message, 422);
    }

    public function internalErrorResponse($data = [], $message = 'Internal server error')
    {
        return $this->formatResponse($data, $message, 500);
    }

    public function deletedResponse($data = [], $message = 'Resource deleted successfully')
    {
        return $this->formatResponse($data, $message, 200);
    }
}
