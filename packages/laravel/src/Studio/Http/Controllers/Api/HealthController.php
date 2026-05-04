<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Studio\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use InfilePhp\Core\Sat\Rtu;
use Exception;

final class HealthController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $start = microtime(true);
        $success = false;
        $error = null;

        $hasSignUser = !empty(env('FEL_SIGN_USER'));
        $hasSignKey = !empty(env('FEL_SIGN_KEY'));
        $hasApiUser = !empty(env('FEL_API_USER'));
        $hasApiKey = !empty(env('FEL_API_KEY'));
        $hasNit = !empty(env('FEL_NIT'));

        $credentialsValid = $hasSignUser && $hasSignKey && $hasApiUser && $hasApiKey && $hasNit;

        if ($credentialsValid) {
            try {
                // A lightweight call to verify the service and authentication
                Rtu::lookupNit(env('FEL_NIT', 'CF'));
                $success = true;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        } else {
            $error = 'Credenciales incompletas en el archivo .env';
        }

        $latency = round((microtime(true) - $start) * 1000);

        return response()->json([
            'success' => true,
            'env' => [
                'sign_user' => $hasSignUser,
                'sign_key' => $hasSignKey,
                'api_user' => $hasApiUser,
                'api_key' => $hasApiKey,
                'nit' => $hasNit,
            ],
            'connection' => [
                'success' => $success,
                'latency_ms' => $latency,
                'error' => $error,
                'environment' => env('FEL_ENV', 'sandbox'),
            ]
        ]);
    }
}
