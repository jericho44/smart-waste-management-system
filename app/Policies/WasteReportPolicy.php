<?php

namespace App\Policies;

use App\Enums\ReportStatus;
use App\Models\User;
use App\Models\WasteReport;

class WasteReportPolicy
{
    /**
     * Determine whether the user can view any reports.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('reports:view-any') || $user->hasPermissionTo('reports:view-own');
    }

    /**
     * Determine whether the user can view the specific report.
     */
    public function view(User $user, WasteReport $report): bool
    {
        if ($user->hasRole('admin') || $user->hasPermissionTo('reports:view-any')) {
            return true;
        }

        return $report->user_id === $user->id;
    }

    /**
     * Determine whether the user can create reports.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('reports:create');
    }

    /**
     * Determine whether the user can update the report.
     */
    public function update(User $user, WasteReport $report): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Citizens can only edit their own report if it is still PENDING
        return $report->user_id === $user->id && $report->status === ReportStatus::Pending;
    }

    /**
     * Determine whether the user can delete the report.
     */
    public function delete(User $user, WasteReport $report): bool
    {
        if ($user->hasRole('admin') || $user->hasPermissionTo('reports:delete-any')) {
            return true;
        }

        // Citizens can only delete their own report if it is still PENDING
        return $report->user_id === $user->id && $report->status === ReportStatus::Pending;
    }

    /**
     * Determine whether the user can update the status of the report.
     */
    public function updateStatus(User $user, WasteReport $report): bool
    {
        // Strictly admin only
        return $user->hasRole('admin') || $user->hasPermissionTo('reports:update-status');
    }

    /**
     * Determine whether the user can upload images to the report.
     */
    public function uploadImages(User $user, WasteReport $report): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $report->user_id === $user->id && $report->status === ReportStatus::Pending;
    }

    /**
     * Determine whether the user can delete an image from the report.
     */
    public function deleteImage(User $user, WasteReport $report): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $report->user_id === $user->id && $report->status === ReportStatus::Pending;
    }
}
