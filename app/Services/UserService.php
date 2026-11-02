<?php

namespace App\Services;

use App\DTOs\Users\CreateUserDTO;
use App\DTOs\Users\UpdateUserDTO;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    /**
     * List users with search, filtering, and pagination.
     *
     * @param  array{search?: string|null, role?: string|null, is_active?: mixed, per_page?: int|null, sort?: string|null, order?: string|null}  $filters
     */
    public function list(array $filters): LengthAwarePaginator
    {
        $query = User::with(['roles', 'permissions']);

        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($q) use ($search, $like): void {
                $q->where('name', $like, $search)
                    ->orWhere('email', $like, $search);
            });
        }

        if (! empty($filters['role'])) {
            $query->role($filters['role']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== null && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        $sort = $filters['sort'] ?? 'created_at';
        $order = strtolower($filters['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $order);

        $perPage = max(1, min((int) ($filters['per_page'] ?? 10), 100));

        return $query->paginate($perPage);
    }

    /**
     * Create a new user account.
     */
    public function create(CreateUserDTO $dto): User
    {
        return DB::transaction(function () use ($dto): User {
            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
                'is_active' => $dto->isActive,
            ]);

            $user->assignRole($dto->role);

            return $user->load('roles', 'permissions');
        });
    }

    /**
     * Update an existing user account.
     */
    public function update(User $user, UpdateUserDTO $dto): User
    {
        return DB::transaction(function () use ($user, $dto): User {
            $updateData = [
                'name' => $dto->name,
                'email' => $dto->email,
            ];

            if ($dto->password !== null) {
                $updateData['password'] = Hash::make($dto->password);
            }

            if ($dto->isActive !== null) {
                $updateData['is_active'] = $dto->isActive;
            }

            $user->update($updateData);

            if ($dto->role !== null) {
                $user->syncRoles([$dto->role]);
            }

            return $user->fresh(['roles', 'permissions']);
        });
    }

    /**
     * Toggle active status of a user.
     */
    public function toggleStatus(User $user, bool $isActive): User
    {
        $user->update(['is_active' => $isActive]);

        return $user->fresh(['roles', 'permissions']);
    }

    /**
     * Soft delete a user account.
     */
    public function delete(User $user): void
    {
        $user->delete();
    }
}
