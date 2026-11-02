<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Users\IndexUserRequest;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\ToggleUserStatusRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseApiController
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display a paginated listing of users.
     */
    public function index(IndexUserRequest $request): JsonResponse
    {
        $paginator = $this->userService->list($request->validated());

        return $this->respondPaginated(
            UserResource::collection($paginator),
            'Users retrieved successfully'
        );
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->toDTO());

        return $this->respondCreated(
            new UserResource($user),
            'User created successfully'
        );
    }

    /**
     * Display the specified user.
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return $this->respondSuccess(
            new UserResource($user->loadMissing(['roles', 'permissions'])),
            'User details retrieved successfully'
        );
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->userService->update($user, $request->toDTO());

        return $this->respondSuccess(
            new UserResource($updatedUser),
            'User updated successfully'
        );
    }

    /**
     * Toggle the active status of a user.
     */
    public function toggleStatus(ToggleUserStatusRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->userService->toggleStatus($user, (bool) $request->validated('is_active'));

        return $this->respondSuccess(
            new UserResource($updatedUser),
            'User status updated successfully'
        );
    }

    /**
     * Soft delete the specified user.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->userService->delete($user);

        return $this->respondSuccess(null, 'User deleted successfully');
    }
}

