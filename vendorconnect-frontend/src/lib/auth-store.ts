import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import apiClient from './api-client';
import { toast } from 'react-hot-toast';
import { EmailService } from './email-service';

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
  forgotPassword: (email: string) => Promise<void>;
  resetPassword: (token: string, email: string, password: string, passwordConfirmation: string) => Promise<void>;
  resendVerification: (email: string) => Promise<void>;
  verifyEmail: (id: string, hash: string) => Promise<void>;
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
          console.log('🔍 [AUTH] Attempting login for:', email);
          const response = await apiClient.post('/auth/login', { email, password });
          const { user, token, permissions } = response.data.data;
          
          console.log('🔍 [AUTH] Login successful, setting token');
          
          // Save to localStorage
          localStorage.setItem('auth_token', token);
          console.log('🔍 [AUTH] Token saved to localStorage');
          
          // Also set as a cookie for middleware
          document.cookie = `auth_token=${token}; path=/; max-age=${7 * 24 * 60 * 60}; SameSite=Lax`;
          console.log('🔍 [AUTH] Token saved as cookie');
          
          set({ user, token, permissions, isLoading: false });
          console.log('🔍 [AUTH] Auth state updated');
          
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
          console.log('checkAuth response:', response.data);
          set({ user: response.data, token });
        } catch (error) {
          console.error('checkAuth error:', error);
          localStorage.removeItem('auth_token');
          document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
          set({ user: null, token: null, permissions: [] });
        }
      },

      forgotPassword: async (email: string) => {
        set({ isLoading: true });
        try {
          await EmailService.forgotPassword({ email });
          toast.success('Password reset link sent to your email!');
        } catch (error: any) {
          console.error('Forgot password error:', error);
          toast.error(error.message || 'Failed to send reset email');
          throw error;
        } finally {
          set({ isLoading: false });
        }
      },

      resetPassword: async (token: string, email: string, password: string, passwordConfirmation: string) => {
        set({ isLoading: true });
        try {
          await EmailService.resetPassword({ token, email, password, password_confirmation: passwordConfirmation });
          toast.success('Password reset successfully!');
        } catch (error: any) {
          console.error('Reset password error:', error);
          toast.error(error.message || 'Failed to reset password');
          throw error;
        } finally {
          set({ isLoading: false });
        }
      },

      resendVerification: async (email: string) => {
        set({ isLoading: true });
        try {
          await EmailService.resendVerification({ email });
          toast.success('Verification email sent!');
        } catch (error: any) {
          console.error('Resend verification error:', error);
          toast.error(error.message || 'Failed to send verification email');
          throw error;
        } finally {
          set({ isLoading: false });
        }
      },

      verifyEmail: async (id: string, hash: string) => {
        set({ isLoading: true });
        try {
          await EmailService.verifyEmail(id, hash);
          toast.success('Email verified successfully!');
        } catch (error: any) {
          console.error('Email verification error:', error);
          toast.error(error.message || 'Failed to verify email');
          throw error;
        } finally {
          set({ isLoading: false });
        }
      },
    }),
    {
      name: 'auth-storage',
      partialize: (state) => ({ user: state.user, permissions: state.permissions }),
    }
  )
);