<?php

namespace App\Services;

use App\Enums\ReportStatus;
use App\Http\Resources\WasteReportResource;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteReport;

class DashboardService
{
    /**
     * Get aggregate analytics data for the admin dashboard.
     *
     * @return array<string, mixed>
     */
    public function getAdminDashboard(): array
    {
        $totalReports = WasteReport::count();
        $totalUsers = User::count();

        $statusCounts = [
            ReportStatus::Pending->value => WasteReport::where('status', ReportStatus::Pending->value)->count(),
            ReportStatus::InProgress->value => WasteReport::where('status', ReportStatus::InProgress->value)->count(),
            ReportStatus::Completed->value => WasteReport::where('status', ReportStatus::Completed->value)->count(),
            ReportStatus::Rejected->value => WasteReport::where('status', ReportStatus::Rejected->value)->count(),
        ];

        $categories = WasteCategory::query()
            ->withCount('reports')
            ->orderByDesc('reports_count')
            ->get()
            ->map(function (WasteCategory $category) use ($totalReports) {
                return [
                    'uuid' => $category->uuid,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'count' => $category->reports_count,
                    'percentage' => $totalReports > 0 ? round(($category->reports_count / $totalReports) * 100, 1) : 0,
                ];
            })
            ->values()
            ->toArray();

        $monthlyStats = $this->getMonthlyStatistics();

        $latestReports = WasteReport::query()
            ->with(['user', 'category', 'images'])
            ->latest()
            ->limit(5)
            ->get();

        return [
            'total_reports' => $totalReports,
            'pending_reports' => $statusCounts[ReportStatus::Pending->value],
            'in_progress_reports' => $statusCounts[ReportStatus::InProgress->value],
            'completed_reports' => $statusCounts[ReportStatus::Completed->value],
            'rejected_reports' => $statusCounts[ReportStatus::Rejected->value],
            'total_users' => $totalUsers,
            'reports_by_category' => $categories,
            'reports_by_status' => $statusCounts,
            'monthly_statistics' => $monthlyStats,
            'latest_reports' => WasteReportResource::collection($latestReports)->resolve(),
        ];
    }

    /**
     * Get personal dashboard data for the authenticated citizen user.
     *
     * @return array<string, mixed>
     */
    public function getUserDashboard(User $user): array
    {
        $userReports = WasteReport::query()->where('user_id', $user->id);

        $totalReports = (clone $userReports)->count();

        $statusCounts = [
            ReportStatus::Pending->value => (clone $userReports)->where('status', ReportStatus::Pending->value)->count(),
            ReportStatus::InProgress->value => (clone $userReports)->where('status', ReportStatus::InProgress->value)->count(),
            ReportStatus::Completed->value => (clone $userReports)->where('status', ReportStatus::Completed->value)->count(),
            ReportStatus::Rejected->value => (clone $userReports)->where('status', ReportStatus::Rejected->value)->count(),
        ];

        $categories = WasteCategory::query()
            ->withCount(['reports' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderByDesc('reports_count')
            ->get()
            ->map(function (WasteCategory $category) use ($totalReports) {
                return [
                    'uuid' => $category->uuid,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'count' => $category->reports_count,
                    'percentage' => $totalReports > 0 ? round(($category->reports_count / $totalReports) * 100, 1) : 0,
                ];
            })
            ->values()
            ->toArray();

        $monthlyStats = $this->getMonthlyStatistics($user->id);

        $latestReports = WasteReport::query()
            ->with(['category', 'images'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return [
            'total_reports' => $totalReports,
            'pending_reports' => $statusCounts[ReportStatus::Pending->value],
            'in_progress_reports' => $statusCounts[ReportStatus::InProgress->value],
            'completed_reports' => $statusCounts[ReportStatus::Completed->value],
            'rejected_reports' => $statusCounts[ReportStatus::Rejected->value],
            'reports_by_category' => $categories,
            'reports_by_status' => $statusCounts,
            'monthly_statistics' => $monthlyStats,
            'latest_reports' => WasteReportResource::collection($latestReports)->resolve(),
        ];
    }

    /**
     * Generate 6-month continuous trend data for reports.
     *
     * @return list<array{month: string, label: string, short_label: string, submitted: int, completed: int}>
     */
    public function getMonthlyStatistics(?int $userId = null): array
    {
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->endOfMonth();

        $reports = WasteReport::query()
            ->select(['id', 'status', 'created_at'])
            ->when($userId !== null, fn ($query) => $query->where('user_id', $userId))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $monthly[$key] = [
                'month' => $key,
                'label' => $date->format('M Y'),
                'short_label' => $date->format('M'),
                'submitted' => 0,
                'completed' => 0,
            ];
        }

        foreach ($reports as $report) {
            if ($report->created_at !== null) {
                $key = $report->created_at->format('Y-m');
                if (isset($monthly[$key])) {
                    $monthly[$key]['submitted']++;
                    if ($report->status === ReportStatus::Completed) {
                        $monthly[$key]['completed']++;
                    }
                }
            }
        }

        return array_values($monthly);
    }
}
