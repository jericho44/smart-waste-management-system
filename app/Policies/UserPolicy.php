<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('users:view') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the specific user.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('users:view') || $user->id === $model->id;
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('users:manage') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the user.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasPermissionTo('users:manage') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can toggle status of the user.
     */
    public function toggleStatus(User $user, User $model): bool
    {
        // Admin cannot deactivate their own account
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermissionTo('users:manage') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the user.
     */
    public function delete(User $user, User $model): bool
    {
        // Admin cannot delete their own account
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermissionTo('users:manage') || $user->hasRole('admin');
    }
}

