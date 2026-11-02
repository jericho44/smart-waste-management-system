<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends BaseApiController
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Get system-wide dashboard metrics for administrators.
     */
    public function admin(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->can('dashboard:view-admin') && ! $user->hasRole(UserRole::Admin->value)) {
            abort(403, 'Unauthorized. Admin dashboard access required.');
        }

        $data = $this->dashboardService->getAdminDashboard();

        return $this->respondSuccess($data, 'Admin dashboard data retrieved successfully');
    }

    /**
     * Get personal dashboard metrics for citizen users.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $this->dashboardService->getUserDashboard($user);

        return $this->respondSuccess($data, 'User dashboard data retrieved successfully');
    }
}

