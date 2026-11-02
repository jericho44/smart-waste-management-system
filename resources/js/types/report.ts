import type { UserProfile } from './api';
import type { WasteCategory } from './category';

export type ReportStatusType = 'PENDING' | 'IN_PROGRESS' | 'COMPLETED' | 'REJECTED';

export interface ReportImage {
  uuid: string;
  url: string;
  original_filename: string;
  mime_type: string;
  file_size: number;
  created_at: string;
}

export interface ReportStatusHistory {
  uuid: string;
  old_status: ReportStatusType | null;
  new_status: ReportStatusType;
  notes: string | null;
  changed_by?: {
    uuid: string;
    name: string;
    email: string;
  };
  created_at: string;
}

export interface WasteReport {
  uuid: string;
  title: string;
  description: string;
  status: ReportStatusType;
  latitude: number;
  longitude: number;
  address: string | null;
  user?: UserProfile;
  category?: WasteCategory;
  images?: ReportImage[];
  status_histories?: ReportStatusHistory[];
  created_at: string;
  updated_at: string;
}

export interface ReportPayload {
  title: string;
  description: string;
  category_uuid: string;
  latitude: number;
  longitude: number;
  address?: string | null;
  images?: File[];
}

export interface TransitionStatusPayload {
  status: ReportStatusType;
  notes?: string | null;
}

