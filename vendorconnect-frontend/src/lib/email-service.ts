import { apiClient } from './api-client';

export interface ForgotPasswordRequest {
  email: string;
}

export interface ResetPasswordRequest {
  token: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface ResendVerificationRequest {
  email: string;
}

export interface SendWelcomeEmailRequest {
  email: string;
}

export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data?: T;
}

export class EmailService {
  /**
   * Send forgot password email
   */
  static async forgotPassword(data: ForgotPasswordRequest): Promise<ApiResponse> {
    try {
      const response = await apiClient.post('/auth/forgot-password', data);
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Failed to send reset email');
    }
  }

  /**
   * Reset password with token
   */
  static async resetPassword(data: ResetPasswordRequest): Promise<ApiResponse> {
    try {
      const response = await apiClient.post('/auth/reset-password', data);
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Failed to reset password');
    }
  }

  /**
   * Resend email verification
   */
  static async resendVerification(data: ResendVerificationRequest): Promise<ApiResponse> {
    try {
      const response = await apiClient.post('/auth/resend-verification', data);
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Failed to send verification email');
    }
  }

  /**
   * Send welcome email
   */
  static async sendWelcomeEmail(data: SendWelcomeEmailRequest): Promise<ApiResponse> {
    try {
      const response = await apiClient.post('/auth/send-welcome-email', data);
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Failed to send welcome email');
    }
  }

  /**
   * Verify email with token and hash
   */
  static async verifyEmail(id: string, hash: string): Promise<ApiResponse> {
    try {
      const response = await apiClient.post(`/auth/verify-email/${id}/${hash}`);
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Failed to verify email');
    }
  }
}

export default EmailService;
