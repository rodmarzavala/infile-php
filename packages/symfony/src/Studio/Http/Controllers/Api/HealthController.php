<?php

declare(strict_types=1);

namespace InfilePhp\Symfony\Studio\Http\Controllers\Api;

use InfilePhp\Core\InfilePhp;
use Symfony\Component\HttpFoundation\JsonResponse;

final class HealthController
{
    public function index(): JsonResponse
    {
        try {
            $ms = InfilePhp::client()->ping();
            return new JsonResponse([
                'status' => 'online',
                'latency_ms' => $ms,
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'status' => 'offline',
                'error' => $e->getMessage(),
            ], 503);
        }
    }
}
