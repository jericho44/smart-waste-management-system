<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Categories\IndexCategoryRequest;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\ToggleCategoryStatusRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Http\Resources\WasteCategoryResource;
use App\Models\WasteCategory;
use App\Services\WasteCategoryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WasteCategoryController extends BaseApiController
{
    public function __construct(
        protected WasteCategoryService $categoryService
    ) {}

    /**
     * Display a listing of waste categories.
     */
    public function index(IndexCategoryRequest $request): JsonResponse
    {
        $forAdmin = $request->user()?->hasRole('admin') ?? false;
        $result = $this->categoryService->list($request->validated(), $forAdmin);

        if ($result instanceof LengthAwarePaginator) {
            return $this->respondPaginated(
                WasteCategoryResource::collection($result),
                'Categories retrieved successfully'
            );
        }

        return $this->respondSuccess(
            WasteCategoryResource::collection($result),
            'Categories retrieved successfully'
        );
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->toDTO());

        return $this->respondCreated(
            new WasteCategoryResource($category),
            'Category created successfully'
        );
    }

    /**
     * Display the specified category.
     */
    public function show(Request $request, WasteCategory $category): JsonResponse
    {
        return $this->respondSuccess(
            new WasteCategoryResource($category->loadCount('reports')),
            'Category details retrieved successfully'
        );
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, WasteCategory $category): JsonResponse
    {
        $updatedCategory = $this->categoryService->update($category, $request->toDTO());

        return $this->respondSuccess(
            new WasteCategoryResource($updatedCategory),
            'Category updated successfully'
        );
    }

    /**
     * Toggle the active status of a category.
     */
    public function toggleStatus(ToggleCategoryStatusRequest $request, WasteCategory $category): JsonResponse
    {
        $updatedCategory = $this->categoryService->toggleStatus($category, (bool) $request->validated('is_active'));

        return $this->respondSuccess(
            new WasteCategoryResource($updatedCategory),
            'Category status updated successfully'
        );
    }

    /**
     * Remove the specified category if business rules allow.
     */
    public function destroy(Request $request, WasteCategory $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $this->categoryService->delete($category);

        return $this->respondSuccess(null, 'Category deleted successfully');
    }
}

