<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WasteCategory;

class WasteCategoryPolicy
{
    /**
     * Determine whether the user can view categories list.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the category.
     */
    public function view(?User $user, WasteCategory $category): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create categories.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('categories:manage') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the category.
     */
    public function update(User $user, WasteCategory $category): bool
    {
        return $user->hasPermissionTo('categories:manage') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can toggle status of the category.
     */
    public function toggleStatus(User $user, WasteCategory $category): bool
    {
        return $user->hasPermissionTo('categories:manage') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the category.
     */
    public function delete(User $user, WasteCategory $category): bool
    {
        return $user->hasPermissionTo('categories:manage') || $user->hasRole('admin');
    }
}
