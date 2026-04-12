<?php

namespace App\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Trait ApiResponse
 *
 * Provides standardized JSON response methods for API controllers.
 *
 * Success envelope:
 * {
 *   "success": true,
 *   "message": "...",
 *   "data": { ... }
 * }
 *
 * Paginated envelope (via paginated()):
 * {
 *   "success": true,
 *   "message": "...",
 *   "data": [ ... ],
 *   "meta": { current_page, per_page, total, last_page, from, to },
 *   "links": { first, last, prev, next }
 * }
 *
 * Error envelope:
 * {
 *   "success": false,
 *   "message": "...",
 *   "errors": { ... }   // optional
 * }
 */
trait ApiResponse
{
    /**
     * 200 OK — generic success with optional data payload.
     *
     * @param  mixed   $data
     * @param  string  $message
     * @param  int     $status
     */
    protected function success(
        mixed $data = null,
        string $message = 'OK',
        int $status = 200,
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if (! is_null($data)) {
            $payload['data'] = $data instanceof JsonResource
                ? $data->resolve(request())
                : $data;
        }

        return response()->json($payload, $status);
    }

    /**
     * 201 Created — resource was successfully created.
     *
     * @param  mixed   $data
     * @param  string  $message
     */
    protected function created(
        mixed $data = null,
        string $message = 'Resource created.',
    ): JsonResponse {
        return $this->success($data, $message, 201);
    }

    /**
     * 204 No Content — action succeeded but nothing to return.
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Paginated list — wraps a LengthAwarePaginator with meta and links.
     *
     * @param  LengthAwarePaginator                    $paginator
     * @param  ResourceCollection|array<mixed>|null    $data       Pass a ResourceCollection
     *                                                              or leave null to use raw items.
     * @param  string                                  $message
     */
    protected function paginated(
        LengthAwarePaginator $paginator,
        ResourceCollection|array|null $data = null,
        string $message = 'OK',
    ): JsonResponse {
        $items = match (true) {
            $data instanceof ResourceCollection => $data->resolve(request()),
            is_array($data)                    => $data,
            default                            => $paginator->items(),
        };

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Generic error response.
     *
     * @param  string              $message
     * @param  array<mixed>|null   $errors   Validation errors or additional context.
     * @param  int                 $status
     */
    protected function error(
        string $message,
        ?array $errors = null,
        int $status = 400,
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * 404 Not Found.
     *
     * @param  string  $message
     */
    protected function notFound(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->error($message, null, 404);
    }

    /**
     * 401 Unauthorized — unauthenticated.
     *
     * @param  string  $message
     */
    protected function unauthorized(string $message = 'Unauthenticated.'): JsonResponse
    {
        return $this->error($message, null, 401);
    }

    /**
     * 403 Forbidden — authenticated but not allowed.
     *
     * @param  string  $message
     */
    protected function forbidden(string $message = 'Forbidden.'): JsonResponse
    {
        return $this->error($message, null, 403);
    }

    /**
     * 422 Unprocessable Entity — validation failed.
     *
     * @param  array<string, mixed>  $errors   Keyed by field name.
     * @param  string                $message
     */
    protected function validationError(
        array $errors,
        string $message = 'The given data was invalid.',
    ): JsonResponse {
        return $this->error($message, $errors, 422);
    }
}
