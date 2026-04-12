<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class HttpExceptionRenderer
{
    protected array $handled = [401, 403, 404, 419, 429, 500, 503];

    protected array $apiMessages = [
        401 => 'Unauthorized.',
        403 => 'Forbidden.',
        404 => 'Resource not found.',
        429 => 'Too many requests.',
        500 => 'Server error.',
        503 => 'Service unavailable.',
    ];

    public function __invoke(HttpExceptionInterface $e, Request $request): ?Response
    {
        $status = $e->getStatusCode();

        if (! in_array($status, $this->handled)) {
            return null;
        }

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: ($this->apiMessages[$status] ?? 'Error.'),
            ], $status);
        }

        return Inertia::render('Error', [
            'status'  => $status,
            'message' => $e->getMessage() ?: null,
        ])->toResponse($request)->setStatusCode($status);
    }
}
