import axios from 'axios';
import { toast } from 'react-hot-toast';

const API_URL = process.env.NEXT_PUBLIC_API_URL;

if (!API_URL) {
  console.warn('NEXT_PUBLIC_API_URL is not defined. Falling back to relative API requests.');
}

export const apiClient = axios.create({
  baseURL: API_URL || undefined,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request interceptor to add auth token
apiClient.interceptors.request.use(
  (config) => {
    const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null;
    if (process.env.NODE_ENV === 'development') {
      console.log('API Request - Token:', token ? 'Present' : 'Missing');
      console.log('API Request - URL:', config.url);
    }
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle errors
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    // Check for suspended membership
    if (error.response?.data?.membership_suspended === true) {
      if (typeof window !== 'undefined') {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        window.location.href = '/suspended';
      }
      return Promise.reject(error);
    }
    
    if (error.response?.status === 401) {
      // Clear token and redirect to login
      if (typeof window !== 'undefined') {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        window.location.href = '/login';
      }
    } else if (error.response?.status === 403) {
      toast.error('You do not have permission to perform this action');
    } else if (error.response?.status === 422) {
      const errors = error.response.data.errors;
      if (errors) {
        Object.keys(errors).forEach((key) => {
          errors[key].forEach((message: string) => {
            toast.error(message);
          });
        });
      }
    } else if (error.response?.status >= 500) {
      toast.error('Server error. Please try again later.');
    }
    return Promise.reject(error);
  }
);

export default apiClient;
