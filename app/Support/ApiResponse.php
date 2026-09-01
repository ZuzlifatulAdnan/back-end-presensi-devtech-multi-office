<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

/**
 * Single place that shapes every JSON payload returned by the mobile API,
 * so the client can rely on one contract: { success, message, data, errors, meta }.
 */
class ApiResponse
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public static function success(mixed $data = null, string $message = 'Success', int $status = 200, array $meta = []): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data instanceof ResourceCollection || $data instanceof LengthAwarePaginator) {
            $resolved = $data instanceof ResourceCollection
                ? $data->response()->getData(true)
                : $data->toArray();

            $payload['data'] = $resolved['data'] ?? [];

            if (isset($resolved['meta'])) {
                // Drop the rendered pagination links; a mobile client only needs
                // the counters to decide whether to request the next page.
                $meta = array_merge(
                    Arr::only($resolved['meta'], ['current_page', 'last_page', 'per_page', 'from', 'to', 'total']),
                    $meta
                );
            } elseif ($data instanceof LengthAwarePaginator) {
                $meta = array_merge([
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                ], $meta);
            }
        } else {
            $payload['data'] = $data;
        }

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     */
    public static function error(string $message, int $status = 400, array $errors = [], mixed $data = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }
}
