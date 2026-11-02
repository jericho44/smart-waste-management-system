<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Reports\IndexReportRequest;
use App\Http\Requests\Reports\StoreReportRequest;
use App\Http\Requests\Reports\TransitionStatusRequest;
use App\Http\Requests\Reports\UpdateReportRequest;
use App\Http\Requests\Reports\UploadReportImagesRequest;
use App\Http\Resources\ReportImageResource;
use App\Http\Resources\ReportStatusHistoryResource;
use App\Http\Resources\WasteReportResource;
use App\Models\ReportImage;
use App\Models\User;
use App\Models\WasteReport;
use App\Services\WasteReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WasteReportController extends BaseApiController
{
    public function __construct(
        protected WasteReportService $reportService
    ) {}

    /**
     * Display a paginated listing of waste reports.
     */
    public function index(IndexReportRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $paginator = $this->reportService->list($user, $request->validated());

        return $this->respondPaginated(
            WasteReportResource::collection($paginator),
            'Waste reports retrieved successfully'
        );
    }

    /**
     * Store a newly created waste report.
     */
    public function store(StoreReportRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $report = $this->reportService->create($user, $request->toDTO());

        return $this->respondCreated(
            new WasteReportResource($report),
            'Waste report submitted successfully'
        );
    }

    /**
     * Display the specified waste report.
     */
    public function show(Request $request, WasteReport $report): JsonResponse
    {
        $this->authorize('view', $report);

        $report->loadMissing(['user', 'category', 'images', 'statusHistories.changer']);

        return $this->respondSuccess(
            new WasteReportResource($report),
            'Waste report details retrieved successfully'
        );
    }

    /**
     * Update the specified waste report.
     */
    public function update(UpdateReportRequest $request, WasteReport $report): JsonResponse
    {
        $updatedReport = $this->reportService->update($report, $request->toDTO());

        return $this->respondSuccess(
            new WasteReportResource($updatedReport),
            'Waste report updated successfully'
        );
    }

    /**
     * Remove the specified waste report.
     */
    public function destroy(Request $request, WasteReport $report): JsonResponse
    {
        $this->authorize('delete', $report);

        $this->reportService->delete($report);

        return $this->respondSuccess(null, 'Waste report deleted successfully');
    }

    /**
     * Transition report status (Admin only).
     */
    public function transitionStatus(TransitionStatusRequest $request, WasteReport $report): JsonResponse
    {
        /** @var User $admin */
        $admin = $request->user();
        $updatedReport = $this->reportService->transitionStatus($report, $admin, $request->toDTO());

        return $this->respondSuccess(
            new WasteReportResource($updatedReport),
            'Report status updated successfully'
        );
    }

    /**
     * View status history of the specified report.
     */
    public function history(Request $request, WasteReport $report): JsonResponse
    {
        $this->authorize('view', $report);

        $history = $report->statusHistories()->with('changer')->orderBy('created_at', 'asc')->get();

        return $this->respondSuccess(
            ReportStatusHistoryResource::collection($history),
            'Report status history retrieved successfully'
        );
    }

    /**
     * Upload additional images to the report.
     */
    public function uploadImages(UploadReportImagesRequest $request, WasteReport $report): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $images = $this->reportService->storeImages($report, $request->file('images'), $user);

        return $this->respondCreated(
            ReportImageResource::collection(collect($images)),
            'Images uploaded successfully'
        );
    }

    /**
     * Delete an image from the report.
     */
    public function deleteImage(Request $request, WasteReport $report, ReportImage $image): JsonResponse
    {
        $this->authorize('deleteImage', $report);

        if ($image->waste_report_id !== $report->id) {
            abort(404, 'Image does not belong to this report');
        }

        $this->reportService->deleteImage($report, $image);

        return $this->respondSuccess(null, 'Image deleted successfully');
    }
}

