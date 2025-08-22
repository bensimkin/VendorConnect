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
          
          localStorage.setItem('auth_token', token);
          set({ user, token, permissions, isLoading: false });
          
          toast.success('Login successful!');
        } catch (error: any) {
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
          localStorage.removeItem('auth_token');
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
