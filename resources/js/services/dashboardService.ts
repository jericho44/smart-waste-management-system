import api from '@/services/api';
import type { ApiResponse } from '@/types/api';
import type { AdminDashboardData, UserDashboardData } from '@/types/dashboard';

export const dashboardService = {
  async fetchAdminDashboard(): Promise<AdminDashboardData> {
    const response = await api.get<ApiResponse<AdminDashboardData>>('/dashboard/admin');
    return response.data.data;
  },

  async fetchUserDashboard(): Promise<UserDashboardData> {
    const response = await api.get<ApiResponse<UserDashboardData>>('/dashboard/user');
    return response.data.data;
  },
};
