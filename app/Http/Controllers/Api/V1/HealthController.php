<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends BaseApiController
{
    /**
     * Health check endpoint
     */
    public function __invoke(): JsonResponse
    {
        $dbStatus = 'connected';
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $dbStatus = 'error: '.$e->getMessage();
        }

        return $this->respondSuccess([
            'service' => config('app.name'),
            'status' => 'operational',
            'database' => $dbStatus,
            'timestamp' => now()->toIso8601String(),
        ], 'System status retrieved successfully');
    }
}
