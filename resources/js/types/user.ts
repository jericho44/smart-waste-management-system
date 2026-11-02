import type { UserProfile } from './api';

export interface UserManagePayload {
  name: string;
  email: string;
  password?: string;
  role: string;
  is_active: boolean;
}

export type UserItem = UserProfile;

