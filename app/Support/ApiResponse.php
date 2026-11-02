<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    /**
     * Standard success response
     */
    public function respondSuccess(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = Response::HTTP_OK,
        array $extraMeta = []
    ): JsonResponse {
        $requestId = request()->header('X-Request-ID') ?? request()->attributes->get('request_id') ?? (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $meta = array_merge([
            'request_id' => $requestId,
            'timestamp' => $timestamp,
        ], $extraMeta);

        // Unwrap Resource if needed
        $resolvedData = $data instanceof JsonResource ? $data->resolve() : $data;

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $resolvedData ?? (object) [],
            'meta' => $meta,
            'errors' => null,
        ], $statusCode)->header('X-Request-ID', $requestId);
    }

    /**
     * Standard created response
     */
    public function respondCreated(
        mixed $data = null,
        string $message = 'Resource created successfully',
        array $extraMeta = []
    ): JsonResponse {
        return $this->respondSuccess($data, $message, Response::HTTP_CREATED, $extraMeta);
    }

    /**
     * Standard paginated response
     */
    public function respondPaginated(
        LengthAwarePaginator|ResourceCollection $paginator,
        string $message = 'Data retrieved successfully',
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        $requestId = request()->header('X-Request-ID') ?? request()->attributes->get('request_id') ?? (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $rawPaginator = $paginator instanceof ResourceCollection
            ? $paginator->resource
            : $paginator;

        $paginationMeta = [
            'current_page' => $rawPaginator->currentPage(),
            'last_page' => $rawPaginator->lastPage(),
            'per_page' => $rawPaginator->perPage(),
            'total' => $rawPaginator->total(),
            'from' => $rawPaginator->firstItem() ?? 0,
            'to' => $rawPaginator->lastItem() ?? 0,
            'has_next' => $rawPaginator->hasMorePages(),
            'has_previous' => $rawPaginator->currentPage() > 1,
        ];

        $meta = [
            'pagination' => $paginationMeta,
            'request_id' => $requestId,
            'timestamp' => $timestamp,
        ];

        $data = $paginator instanceof ResourceCollection
            ? $paginator->response()->getData(true)['data']
            : $rawPaginator->items();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
            'errors' => null,
        ], $statusCode)->header('X-Request-ID', $requestId);
    }

    /**
     * Standard error response
     */
    public function respondError(
        string $message = 'An error occurred',
        int $statusCode = Response::HTTP_BAD_REQUEST,
        mixed $errors = null,
        array $extraMeta = []
    ): JsonResponse {
        $requestId = request()->header('X-Request-ID') ?? request()->attributes->get('request_id') ?? (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $meta = array_merge([
            'request_id' => $requestId,
            'timestamp' => $timestamp,
        ], $extraMeta);

        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'meta' => $meta,
            'errors' => $errors,
        ], $statusCode)->header('X-Request-ID', $requestId);
    }
}
