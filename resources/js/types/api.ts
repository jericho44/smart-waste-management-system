export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
  has_next: boolean;
  has_previous: boolean;
}

export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data: T;
  meta: {
    request_id?: string;
    timestamp?: string;
    [key: string]: any;
  } | null;
  errors: Record<string, string[]> | null;
}

export interface ApiPaginatedResponse<T = any> {
  success: boolean;
  message: string;
  data: T[];
  meta: {
    pagination: PaginationMeta;
    request_id?: string;
    timestamp?: string;
    [key: string]: any;
  };
  errors: null;
}

export interface UserProfile {
  uuid: string;
  name: string;
  email: string;
  is_active: boolean;
  roles: string[];
  permissions: string[];
  created_at: string;
  updated_at: string;
}

export interface AuthResponseData {
  user: UserProfile;
  token: string;
  token_type: string;
}
