import api from '@/services/api';
import type { ApiPaginatedResponse, ApiResponse } from '@/types/api';
import type { CategoryPayload, WasteCategory } from '@/types/category';

export const categoryService = {
  async fetchCategories(params?: {
    search?: string;
    is_active?: boolean;
    page?: number;
    per_page?: number;
    sort?: string;
    order?: string;
    all?: boolean;
  }): Promise<ApiPaginatedResponse<WasteCategory> | ApiResponse<WasteCategory[]>> {
    const response = await api.get('/categories', { params });
    return response.data;
  },

  async fetchCategory(uuid: string): Promise<WasteCategory> {
    const response = await api.get<ApiResponse<WasteCategory>>(`/categories/${uuid}`);
    return response.data.data;
  },

  async createCategory(payload: CategoryPayload): Promise<WasteCategory> {
    const response = await api.post<ApiResponse<WasteCategory>>('/categories', payload);
    return response.data.data;
  },

  async updateCategory(uuid: string, payload: CategoryPayload): Promise<WasteCategory> {
    const response = await api.put<ApiResponse<WasteCategory>>(`/categories/${uuid}`, payload);
    return response.data.data;
  },

  async toggleCategoryStatus(uuid: string, isActive: boolean): Promise<WasteCategory> {
    const response = await api.patch<ApiResponse<WasteCategory>>(`/categories/${uuid}/status`, {
      is_active: isActive,
    });
    return response.data.data;
  },

  async deleteCategory(uuid: string): Promise<void> {
    await api.delete(`/categories/${uuid}`);
  },
};

