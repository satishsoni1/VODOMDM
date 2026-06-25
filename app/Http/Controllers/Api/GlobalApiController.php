<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class GlobalApiController extends Controller
{
    protected function ok(mixed $data, string $message = 'OK', array $meta = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => $meta,
        ]);
    }

    protected function created(mixed $data, string $message = 'Created'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], 201);
    }

    protected function fail(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }

    protected function notFound(string $message = 'Not found'): JsonResponse
    {
        return $this->fail($message, 404);
    }

    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->fail($message, 401);
    }

    /** Log an inbound API call and return the log entry for further step updates */
    protected function startLog(Request $request, string $type, string $action, array $extra = []): ApiLog
    {
        return ApiLog::create(array_merge([
            'type'         => $type,
            'action'       => $action,
            'status'       => 'running',
            'triggered_by' => $request->user()?->id,
            'ip_address'   => $request->ip(),
            'request_data' => $this->safeRequestData($request),
        ], $extra));
    }

    private function safeRequestData(Request $request): array
    {
        return collect($request->except(['password', 'token', 'api_token', 'api_key']))
            ->map(fn ($v) => is_string($v) && strlen($v) > 500 ? substr($v, 0, 500) . '…' : $v)
            ->toArray();
    }
}
