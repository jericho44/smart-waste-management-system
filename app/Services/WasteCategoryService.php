<?php

namespace App\Services;

use App\DTOs\Categories\CreateCategoryDTO;
use App\DTOs\Categories\UpdateCategoryDTO;
use App\Models\WasteCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WasteCategoryService
{
    /**
     * List categories with search, filter, and pagination or collection.
     *
     * @param  array{search?: string|null, is_active?: mixed, per_page?: int|null, sort?: string|null, order?: string|null, all?: mixed}  $filters
     */
    public function list(array $filters, bool $forAdmin = false): LengthAwarePaginator|Collection
    {
        $query = WasteCategory::withCount('reports');

        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($q) use ($search, $like): void {
                $q->where('name', $like, $search)
                    ->orWhere('description', $like, $search)
                    ->orWhere('slug', $like, $search);
            });
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== null && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        } elseif (! $forAdmin) {
            // Non-admin by default only receives active categories
            $query->where('is_active', true);
        }

        $sort = $filters['sort'] ?? 'name';
        $order = strtolower($filters['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sort, $order);

        if (! empty($filters['all']) && filter_var($filters['all'], FILTER_VALIDATE_BOOLEAN)) {
            return $query->get();
        }

        $perPage = max(1, min((int) ($filters['per_page'] ?? 10), 100));

        return $query->paginate($perPage);
    }

    /**
     * Create a new waste category.
     */
    public function create(CreateCategoryDTO $dto): WasteCategory
    {
        return WasteCategory::create([
            'uuid' => (string) Str::uuid(),
            'name' => $dto->name,
            'slug' => $dto->slug,
            'description' => $dto->description,
            'is_active' => $dto->isActive,
        ]);
    }

    /**
     * Update an existing category.
     */
    public function update(WasteCategory $category, UpdateCategoryDTO $dto): WasteCategory
    {
        $updateData = ['name' => $dto->name];

        if ($dto->slug !== null) {
            $updateData['slug'] = $dto->slug;
        }

        if ($dto->description !== null) {
            $updateData['description'] = $dto->description;
        }

        if ($dto->isActive !== null) {
            $updateData['is_active'] = $dto->isActive;
        }

        $category->update($updateData);

        return $category->fresh();
    }

    /**
     * Toggle active status of a category.
     */
    public function toggleStatus(WasteCategory $category, bool $isActive): WasteCategory
    {
        $category->update(['is_active' => $isActive]);

        return $category->fresh();
    }

    /**
     * Delete a category only when business rules allow it.
     *
     * @throws ValidationException
     */
    public function delete(WasteCategory $category): void
    {
        if ($category->reports()->exists()) {
            throw ValidationException::withMessages([
                'category' => ['Cannot delete category that has existing waste reports. Please deactivate it instead.'],
            ]);
        }

        $category->delete();
    }
}
