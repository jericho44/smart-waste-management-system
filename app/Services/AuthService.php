<?php

namespace App\Services;

use App\DTOs\Auth\ChangePasswordDTO;
use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\DTOs\Auth\UpdateProfileDTO;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    /**
     * Register a new citizen user.
     *
     * @return array{user: User, token: string}
     */
    public function register(RegisterDTO $dto): array
    {
        return DB::transaction(function () use ($dto): array {
            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
                'is_active' => true,
            ]);

            $user->assignRole(UserRole::User->value);

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user->load('roles', 'permissions'),
                'token' => $token,
            ];
        });
    }

    /**
     * Authenticate a user and generate an access token.
     *
     * @return array{user: User, token: string}
     *
     * @throws ValidationException
     */
    public function login(LoginDTO $dto): array
    {
        $user = User::with(['roles', 'permissions'])
            ->where('email', $dto->email)
            ->first();

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact an administrator.'],
            ]);
        }

        $tokenName = $dto->deviceName ? 'auth_token:'.$dto->deviceName : 'auth_token';
        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Invalidate current user token.
     */
    public function logout(User $user): void
    {
        /** @var PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();
        if ($token) {
            $token->delete();
        }
    }

    /**
     * Update user profile information.
     */
    public function updateProfile(User $user, UpdateProfileDTO $dto): User
    {
        $user->update([
            'name' => $dto->name,
            'email' => $dto->email,
        ]);

        return $user->fresh(['roles', 'permissions']);
    }

    /**
     * Change user password.
     */
    public function changePassword(User $user, ChangePasswordDTO $dto): void
    {
        $user->update([
            'password' => Hash::make($dto->newPassword),
        ]);
    }
}
