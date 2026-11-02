import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/services/api';
import type { ApiResponse, AuthResponseData, UserProfile } from '@/types/api';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<UserProfile | null>(null);
  const token = ref<string | null>(localStorage.getItem('auth_token'));
  const isLoading = ref<boolean>(false);
  const isInitialized = ref<boolean>(false);

  const isAuthenticated = computed(() => !!token.value);
  const isAdmin = computed(() => user.value?.roles?.includes('admin') ?? false);
  const isCitizen = computed(() => user.value?.roles?.includes('user') ?? false);

  function setToken(newToken: string | null) {
    token.value = newToken;
    if (newToken) {
      localStorage.setItem('auth_token', newToken);
    } else {
      localStorage.removeItem('auth_token');
    }
  }

  function setUser(newUser: UserProfile | null) {
    user.value = newUser;
  }

  async function fetchCurrentUser(): Promise<UserProfile | null> {
    if (!token.value) {
      user.value = null;
      isInitialized.value = true;
      return null;
    }

    try {
      isLoading.value = true;
      const response = await api.get<ApiResponse<UserProfile>>('/auth/me');
      if (response.data.success) {
        user.value = response.data.data;
        return response.data.data;
      }
      return null;
    } catch {
      setToken(null);
      user.value = null;
      return null;
    } finally {
      isLoading.value = false;
      isInitialized.value = true;
    }
  }

  async function login(payload: { email: string; password: string; device_name?: string }): Promise<AuthResponseData> {
    isLoading.value = true;
    try {
      const response = await api.post<ApiResponse<AuthResponseData>>('/auth/login', payload);
      const data = response.data.data;
      setToken(data.token);
      setUser(data.user);
      return data;
    } finally {
      isLoading.value = false;
    }
  }

  async function register(payload: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
  }): Promise<AuthResponseData> {
    isLoading.value = true;
    try {
      const response = await api.post<ApiResponse<AuthResponseData>>('/auth/register', payload);
      const data = response.data.data;
      setToken(data.token);
      setUser(data.user);
      return data;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateProfile(payload: { name: string; email: string }): Promise<UserProfile> {
    isLoading.value = true;
    try {
      const response = await api.put<ApiResponse<UserProfile>>('/auth/profile', payload);
      const updatedUser = response.data.data;
      setUser(updatedUser);
      return updatedUser;
    } finally {
      isLoading.value = false;
    }
  }

  async function changePassword(payload: {
    current_password: string;
    new_password: string;
    new_password_confirmation: string;
  }): Promise<void> {
    isLoading.value = true;
    try {
      await api.put<ApiResponse<null>>('/auth/change-password', payload);
    } finally {
      isLoading.value = false;
    }
  }

  async function logout(): Promise<void> {
    try {
      if (token.value) {
        await api.post('/auth/logout');
      }
    } catch {
      // Continue cleanup on failure
    } finally {
      setToken(null);
      setUser(null);
    }
  }

  return {
    user,
    token,
    isLoading,
    isInitialized,
    isAuthenticated,
    isAdmin,
    isCitizen,
    setToken,
    setUser,
    fetchCurrentUser,
    login,
    register,
    updateProfile,
    changePassword,
    logout,
  };
});
