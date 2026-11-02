<?php

namespace App\Services;

use App\DTOs\Reports\CreateReportDTO;
use App\DTOs\Reports\TransitionStatusDTO;
use App\DTOs\Reports\UpdateReportDTO;
use App\Enums\ReportStatus;
use App\Models\ReportImage;
use App\Models\ReportStatusHistory;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WasteReportService
{
    /**
     * List waste reports with filtering, search, and pagination.
     *
     * @param  array{search?: string|null, status?: string|null, category_uuid?: string|null, date_from?: string|null, date_to?: string|null, per_page?: int|null, sort?: string|null, order?: string|null}  $filters
     */
    public function list(User $user, array $filters): LengthAwarePaginator
    {
        $query = WasteReport::with(['user', 'category', 'images']);

        // Scope reports based on role: citizens only see their own reports
        if (! $user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($q) use ($search, $like): void {
                $q->where('title', $like, $search)
                    ->orWhere('description', $like, $search)
                    ->orWhere('address', $like, $search);
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['category_uuid'])) {
            $query->whereHas('category', function ($q) use ($filters): void {
                $q->where('uuid', $filters['category_uuid']);
            });
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $sort = $filters['sort'] ?? 'created_at';
        $order = strtolower($filters['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $order);

        $perPage = max(1, min((int) ($filters['per_page'] ?? 10), 100));

        return $query->paginate($perPage);
    }

    /**
     * Create a new waste report.
     */
    public function create(User $user, CreateReportDTO $dto): WasteReport
    {
        return DB::transaction(function () use ($user, $dto): WasteReport {
            /** @var WasteCategory $category */
            $category = WasteCategory::where('uuid', $dto->categoryUuid)->firstOrFail();

            $report = WasteReport::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'waste_category_id' => $category->id,
                'title' => $dto->title,
                'description' => $dto->description,
                'status' => ReportStatus::Pending,
                'latitude' => $dto->latitude,
                'longitude' => $dto->longitude,
                'address' => $dto->address,
            ]);

            // Save initial status history record
            ReportStatusHistory::create([
                'uuid' => (string) Str::uuid(),
                'waste_report_id' => $report->id,
                'changed_by' => $user->id,
                'old_status' => null,
                'new_status' => ReportStatus::Pending->value,
                'notes' => 'Report created by citizen',
                'created_at' => now(),
            ]);

            if (! empty($dto->images)) {
                $this->storeImages($report, $dto->images, $user);
            }

            return $report->load(['user', 'category', 'images', 'statusHistories.changer']);
        });
    }

    /**
     * Update an existing report.
     */
    public function update(WasteReport $report, UpdateReportDTO $dto): WasteReport
    {
        /** @var WasteCategory $category */
        $category = WasteCategory::where('uuid', $dto->categoryUuid)->firstOrFail();

        $report->update([
            'waste_category_id' => $category->id,
            'title' => $dto->title,
            'description' => $dto->description,
            'latitude' => $dto->latitude,
            'longitude' => $dto->longitude,
            'address' => $dto->address,
        ]);

        return $report->fresh(['user', 'category', 'images', 'statusHistories.changer']);
    }

    /**
     * Soft delete a waste report.
     */
    public function delete(WasteReport $report): void
    {
        $report->delete();
    }

    /**
     * Transition report status and log audit history.
     *
     * @throws ValidationException
     */
    public function transitionStatus(WasteReport $report, User $admin, TransitionStatusDTO $dto): WasteReport
    {
        if (! $report->status->canTransitionTo($dto->newStatus)) {
            throw ValidationException::withMessages([
                'status' => ["Cannot transition status from {$report->status->value} to {$dto->newStatus->value}."],
            ]);
        }

        return DB::transaction(function () use ($report, $admin, $dto): WasteReport {
            $oldStatus = $report->status->value;

            ReportStatusHistory::create([
                'uuid' => (string) Str::uuid(),
                'waste_report_id' => $report->id,
                'changed_by' => $admin->id,
                'old_status' => $oldStatus,
                'new_status' => $dto->newStatus->value,
                'notes' => $dto->notes,
                'created_at' => now(),
            ]);

            $report->update([
                'status' => $dto->newStatus,
            ]);

            return $report->fresh(['user', 'category', 'images', 'statusHistories.changer']);
        });
    }

    /**
     * Upload additional images to a report.
     *
     * @param  list<UploadedFile>  $images
     * @return list<ReportImage>
     */
    public function storeImages(WasteReport $report, array $images, ?User $user = null): array
    {
        $createdImages = [];

        foreach ($images as $file) {
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $uniqueFilename = (string) Str::uuid().'.'.$extension;
            $path = $file->storeAs('reports/'.$report->uuid, $uniqueFilename, 'public');

            $imageRecord = ReportImage::create([
                'uuid' => (string) Str::uuid(),
                'waste_report_id' => $report->id,
                'image_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: 'image/jpeg',
                'file_size' => $file->getSize() ?: 0,
                'created_by' => $user?->id,
            ]);

            $createdImages[] = $imageRecord;
        }

        return $createdImages;
    }

    /**
     * Delete an image from a report.
     */
    public function deleteImage(WasteReport $report, ReportImage $image): void
    {
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();
    }
}
