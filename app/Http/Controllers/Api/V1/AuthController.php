<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends BaseApiController
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new citizen account.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->toDTO());

        return $this->respondCreated([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Registration successful');
    }

    /**
     * Authenticate user credentials and return access token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->toDTO());

        return $this->respondSuccess([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    /**
     * Logout and revoke current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->authService->logout($user);

        return $this->respondSuccess(null, 'Successfully logged out');
    }

    /**
     * Get profile of authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->loadMissing(['roles', 'permissions']);

        return $this->respondSuccess(
            new UserResource($user),
            'Profile retrieved successfully'
        );
    }

    /**
     * Update user profile information.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $updatedUser = $this->authService->updateProfile($user, $request->toDTO());

        return $this->respondSuccess(
            new UserResource($updatedUser),
            'Profile updated successfully'
        );
    }

    /**
     * Change user password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->authService->changePassword($user, $request->toDTO());

        return $this->respondSuccess(null, 'Password changed successfully');
    }
}
