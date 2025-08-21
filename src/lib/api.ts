import axios from 'axios';

const API_BASE_URL = 'https://vc.themastermind.com.au/api/v1';

// Create axios instance
export const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor to add auth token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor for error handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expired or invalid
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// API endpoints
export const endpoints = {
  auth: {
    login: '/auth/login',
    logout: '/auth/logout',
  },
  dashboard: '/dashboard',
  tasks: '/tasks',
  taskTypes: '/task-types',
  statuses: '/statuses',
  priorities: '/priorities',
  users: '/users',
  userRoles: '/user-roles',
  clients: '/clients',
  tags: '/tags',
  profile: '/profile',
  notifications: '/notifications',
  projects: '/projects',
  taskBriefTemplates: '/task-brief-templates',
  taskBriefQuestions: '/task-brief-questions',
  taskBriefChecklists: '/task-brief-checklists',
};

// API response types
export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data: T;
}

export interface PaginatedResponse<T = any> {
  success: boolean;
  message: string;
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}
