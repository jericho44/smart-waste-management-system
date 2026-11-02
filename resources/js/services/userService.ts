import api from '@/services/api';
import type { ApiPaginatedResponse, ApiResponse, UserProfile } from '@/types/api';
import type { UserManagePayload } from '@/types/user';

export const userService = {
  async fetchUsers(params?: {
    search?: string;
    role?: string;
    is_active?: boolean;
    page?: number;
    per_page?: number;
    sort?: string;
    order?: string;
  }): Promise<ApiPaginatedResponse<UserProfile>> {
    const response = await api.get('/users', { params });
    return response.data;
  },

  async fetchUser(uuid: string): Promise<UserProfile> {
    const response = await api.get<ApiResponse<UserProfile>>(`/users/${uuid}`);
    return response.data.data;
  },

  async createUser(payload: UserManagePayload): Promise<UserProfile> {
    const response = await api.post<ApiResponse<UserProfile>>('/users', payload);
    return response.data.data;
  },

  async updateUser(uuid: string, payload: Partial<UserManagePayload>): Promise<UserProfile> {
    const response = await api.put<ApiResponse<UserProfile>>(`/users/${uuid}`, payload);
    return response.data.data;
  },

  async toggleUserStatus(uuid: string, isActive: boolean): Promise<UserProfile> {
    const response = await api.patch<ApiResponse<UserProfile>>(`/users/${uuid}/status`, {
      is_active: isActive,
    });
    return response.data.data;
  },

  async deleteUser(uuid: string): Promise<void> {
    await api.delete(`/users/${uuid}`);
  },
};

