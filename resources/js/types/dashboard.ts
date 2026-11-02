import type { WasteReport } from './report';

export interface CategoryStatistic {
  uuid: string;
  name: string;
  slug: string;
  count: number;
  percentage: number;
}

export interface MonthlyStatistic {
  month: string;
  label: string;
  short_label: string;
  submitted: number;
  completed: number;
}

export interface StatusCounts {
  PENDING: number;
  IN_PROGRESS: number;
  COMPLETED: number;
  REJECTED: number;
}

export interface AdminDashboardData {
  total_reports: number;
  pending_reports: number;
  in_progress_reports: number;
  completed_reports: number;
  rejected_reports: number;
  total_users: number;
  reports_by_category: CategoryStatistic[];
  reports_by_status: StatusCounts;
  monthly_statistics: MonthlyStatistic[];
  latest_reports: WasteReport[];
}

export interface UserDashboardData {
  total_reports: number;
  pending_reports: number;
  in_progress_reports: number;
  completed_reports: number;
  rejected_reports: number;
  reports_by_category: CategoryStatistic[];
  reports_by_status: StatusCounts;
  monthly_statistics: MonthlyStatistic[];
  latest_reports: WasteReport[];
}
