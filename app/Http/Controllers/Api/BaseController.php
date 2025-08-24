<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    /**
     * Success response
     */
    public function sendResponse($result, $message = 'Success')
    {
        \Log::info('=== SEND RESPONSE START ===');
        \Log::info('BaseController::sendResponse called with message: ' . $message);
        \Log::info('Response data type: ' . gettype($result));
        \Log::info('Response data class: ' . (is_object($result) ? get_class($result) : 'not object'));
        
        try {
            $response = [
                'success' => true,
                'message' => $message,
                'data' => $result
            ];

            \Log::info('Response structure: ' . json_encode($response));
            \Log::info('=== SEND RESPONSE SUCCESS ===');
            return response()->json($response, 200);
        } catch (\Exception $e) {
            \Log::error('=== SEND RESPONSE ERROR ===');
            \Log::error('Error in sendResponse: ' . $e->getMessage());
            \Log::error('Error class: ' . get_class($e));
            \Log::error('Error file: ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('=== SEND RESPONSE ERROR END ===');
            
            // Fallback response
            return response()->json([
                'success' => false,
                'message' => 'Internal server error during response generation',
                'data' => null
            ], 500);
        }
    }

    /**
     * Error response
     */
    public function sendError($error, $errorMessages = [], $code = 400)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    /**
     * Paginated response
     */
    public function sendPaginatedResponse($data, $message = 'Success')
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ]
        ];

        return response()->json($response, 200);
    }

    /**
     * Validation error response
     */
    public function sendValidationError($errors, $message = 'Validation failed')
    {
        return $this->sendError($message, $errors, 422);
    }

    /**
     * Not found response
     */
    public function sendNotFound($message = 'Resource not found')
    {
        return $this->sendError($message, [], 404);
    }

    /**
     * Unauthorized response
     */
    public function sendUnauthorized($message = 'Unauthorized')
    {
        return $this->sendError($message, [], 401);
    }

    /**
     * Forbidden response
     */
    public function sendForbidden($message = 'Forbidden')
    {
        return $this->sendError($message, [], 403);
    }

    /**
     * Server error response
     */
    public function sendServerError($message = 'Internal server error')
    {
        \Log::error('=== SERVER ERROR RESPONSE ===');
        \Log::error('sendServerError called with message: ' . $message);
        \Log::error('Request URL: ' . request()->fullUrl());
        \Log::error('Request method: ' . request()->method());
        \Log::error('Auth user: ' . json_encode(Auth::user()));
        \Log::error('=== SERVER ERROR RESPONSE END ===');
        return $this->sendError($message, [], 500);
    }
}
