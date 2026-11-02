import api from '@/services/api';
import type { ApiPaginatedResponse, ApiResponse } from '@/types/api';
import type {
  ReportPayload,
  ReportStatusHistory,
  ReportStatusType,
  TransitionStatusPayload,
  WasteReport,
} from '@/types/report';

export const reportService = {
  async fetchReports(params?: {
    search?: string;
    status?: ReportStatusType | string;
    category_uuid?: string;
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
    sort?: string;
    order?: string;
  }): Promise<ApiPaginatedResponse<WasteReport>> {
    const response = await api.get('/reports', { params });
    return response.data;
  },

  async fetchReport(uuid: string): Promise<WasteReport> {
    const response = await api.get<ApiResponse<WasteReport>>(`/reports/${uuid}`);
    return response.data.data;
  },

  async createReport(payload: ReportPayload): Promise<WasteReport> {
    const formData = new FormData();
    formData.append('title', payload.title);
    formData.append('description', payload.description);
    formData.append('category_uuid', payload.category_uuid);
    formData.append('latitude', payload.latitude.toString());
    formData.append('longitude', payload.longitude.toString());
    if (payload.address) {
      formData.append('address', payload.address);
    }

    if (payload.images && payload.images.length > 0) {
      payload.images.forEach((file) => {
        formData.append('images[]', file);
      });
    }

    const response = await api.post<ApiResponse<WasteReport>>('/reports', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    return response.data.data;
  },

  async updateReport(uuid: string, payload: Partial<ReportPayload>): Promise<WasteReport> {
    const response = await api.put<ApiResponse<WasteReport>>(`/reports/${uuid}`, payload);
    return response.data.data;
  },

  async deleteReport(uuid: string): Promise<void> {
    await api.delete(`/reports/${uuid}`);
  },

  async transitionStatus(uuid: string, payload: TransitionStatusPayload): Promise<WasteReport> {
    const response = await api.patch<ApiResponse<WasteReport>>(`/reports/${uuid}/status`, payload);
    return response.data.data;
  },

  async fetchHistory(uuid: string): Promise<ReportStatusHistory[]> {
    const response = await api.get<ApiResponse<ReportStatusHistory[]>>(`/reports/${uuid}/history`);
    return response.data.data;
  },

  async uploadImages(uuid: string, files: File[]): Promise<void> {
    const formData = new FormData();
    files.forEach((file) => {
      formData.append('images[]', file);
    });

    await api.post(`/reports/${uuid}/images`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
  },

  async deleteImage(reportUuid: string, imageUuid: string): Promise<void> {
    await api.delete(`/reports/${reportUuid}/images/${imageUuid}`);
  },
};

