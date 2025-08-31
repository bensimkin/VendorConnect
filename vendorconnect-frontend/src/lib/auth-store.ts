import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import apiClient from './api-client';
import { toast } from 'react-hot-toast';

interface User {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  photo: string | null;
  status: number;
  roles?: Array<{ id: number; name: string }>;
}

interface AuthState {
  user: User | null;
  token: string | null;
  permissions: string[];
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  checkAuth: () => Promise<void>;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      user: null,
      token: null,
      permissions: [],
      isLoading: false,

      login: async (email: string, password: string) => {
        set({ isLoading: true });
        try {
          const response = await apiClient.post('/auth/login', { email, password });
          const { user, token, permissions } = response.data.data;
          
          // Save to localStorage
          localStorage.setItem('auth_token', token);
          
          // Also set as a cookie for middleware
          document.cookie = `auth_token=${token}; path=/; max-age=${7 * 24 * 60 * 60}; SameSite=Lax`;
          
          set({ user, token, permissions, isLoading: false });
          
          toast.success('Login successful!');
        } catch (error: any) {
          console.error('🔍 [AUTH] Login error:', error);
          set({ isLoading: false });
          throw error;
        }
      },

      logout: async () => {
        try {
          await apiClient.post('/auth/logout');
        } catch (error) {
          console.error('Logout error:', error);
        } finally {
          localStorage.removeItem('auth_token');
          // Remove cookie
          document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
          set({ user: null, token: null, permissions: [] });
          window.location.href = '/login';
        }
      },

      checkAuth: async () => {
        const token = localStorage.getItem('auth_token');
        if (!token) {
          set({ user: null, token: null, permissions: [] });
          return;
        }

        try {
          const response = await apiClient.get('/user');
          set({ user: response.data, token });
        } catch (error) {
          console.error('checkAuth error:', error);
          localStorage.removeItem('auth_token');
          document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
          set({ user: null, token: null, permissions: [] });
        }
      },
    }),
    {
      name: 'auth-storage',
      partialize: (state) => ({ user: state.user, permissions: state.permissions }),
    }
  )
);