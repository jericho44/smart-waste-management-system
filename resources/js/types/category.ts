export interface WasteCategory {
  uuid: string;
  name: string;
  slug: string;
  description: string | null;
  is_active: boolean;
  reports_count?: number;
  created_at: string;
  updated_at: string;
}

export interface CategoryPayload {
  name: string;
  slug?: string;
  description?: string | null;
  is_active?: boolean;
}

