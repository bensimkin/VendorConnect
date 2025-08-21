import axios from 'axios';

// Create axios instance
const apiClient = axios.create({
  baseURL: process.env.REACT_APP_API_URL || 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor to add auth token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
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
  (response) => {
    return response;
  },
  (error) => {
    if (error.response?.status === 401) {
      // Token expired or invalid
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Auth API
export const authAPI = {
  login: (credentials) => apiClient.post('/auth/login', credentials),
  logout: () => apiClient.post('/auth/logout'),
  forgotPassword: (email) => apiClient.post('/auth/forgot-password', { email }),
  resetPassword: (data) => apiClient.post('/auth/reset-password', data),
  verifyEmail: (id, hash) => apiClient.post(`/auth/verify-email/${id}/${hash}`),
  resendVerification: (email) => apiClient.post('/auth/resend-verification', { email }),
};

// Tasks API
export const tasksAPI = {
  getAll: (params) => apiClient.get('/tasks', { params }),
  getById: (id) => apiClient.get(`/tasks/${id}`),
  create: (data) => apiClient.post('/tasks', data),
  update: (id, data) => apiClient.put(`/tasks/${id}`, data),
  delete: (id) => apiClient.delete(`/tasks/${id}`),
  deleteMultiple: (taskIds) => apiClient.delete('/tasks', { data: { task_ids: taskIds } }),
  updateStatus: (id, statusId) => apiClient.put(`/tasks/${id}/status`, { status_id: statusId }),
  updateDeadline: (id, endDate) => apiClient.put(`/tasks/${id}/deadline`, { end_date: endDate }),
  getInformation: (id) => apiClient.get(`/tasks/${id}/information`),
};

// Users API
export const usersAPI = {
  getAll: (params) => apiClient.get('/users', { params }),
  getById: (id) => apiClient.get(`/users/${id}`),
  create: (data) => apiClient.post('/users', data),
  update: (id, data) => apiClient.put(`/users/${id}`, data),
  delete: (id) => apiClient.delete(`/users/${id}`),
  deleteMultiple: (userIds) => apiClient.delete('/users', { data: { user_ids: userIds } }),
};

// Clients API
export const clientsAPI = {
  getAll: (params) => apiClient.get('/clients', { params }),
  getById: (id) => apiClient.get(`/clients/${id}`),
  create: (data) => apiClient.post('/clients', data),
  update: (id, data) => apiClient.put(`/clients/${id}`, data),
  delete: (id) => apiClient.delete(`/clients/${id}`),
  deleteMultiple: (clientIds) => apiClient.delete('/clients', { data: { client_ids: clientIds } }),
  deleteFile: (fileId) => apiClient.delete(`/clients/files/${fileId}`),
};

// Statuses API
export const statusesAPI = {
  getAll: (params) => apiClient.get('/statuses', { params }),
  getById: (id) => apiClient.get(`/statuses/${id}`),
  create: (data) => apiClient.post('/statuses', data),
  update: (id, data) => apiClient.put(`/statuses/${id}`, data),
  delete: (id) => apiClient.delete(`/statuses/${id}`),
  deleteMultiple: (statusIds) => apiClient.delete('/statuses', { data: { status_ids: statusIds } }),
};

// Priorities API
export const prioritiesAPI = {
  getAll: (params) => apiClient.get('/priorities', { params }),
  getById: (id) => apiClient.get(`/priorities/${id}`),
  create: (data) => apiClient.post('/priorities', data),
  update: (id, data) => apiClient.put(`/priorities/${id}`, data),
  delete: (id) => apiClient.delete(`/priorities/${id}`),
  deleteMultiple: (priorityIds) => apiClient.delete('/priorities', { data: { priority_ids: priorityIds } }),
};

// Tags API
export const tagsAPI = {
  getAll: (params) => apiClient.get('/tags', { params }),
  getById: (id) => apiClient.get(`/tags/${id}`),
  create: (data) => apiClient.post('/tags', data),
  update: (id, data) => apiClient.put(`/tags/${id}`, data),
  delete: (id) => apiClient.delete(`/tags/${id}`),
};

// Task Types API
export const taskTypesAPI = {
  getAll: (params) => apiClient.get('/task-types', { params }),
  getById: (id) => apiClient.get(`/task-types/${id}`),
  create: (data) => apiClient.post('/task-types', data),
  update: (id, data) => apiClient.put(`/task-types/${id}`, data),
  delete: (id) => apiClient.delete(`/task-types/${id}`),
};

// User Roles API
export const userRolesAPI = {
  getAll: (params) => apiClient.get('/user-roles', { params }),
  getById: (id) => apiClient.get(`/user-roles/${id}`),
  create: (data) => apiClient.post('/user-roles', data),
  update: (id, data) => apiClient.put(`/user-roles/${id}`, data),
  delete: (id) => apiClient.delete(`/user-roles/${id}`),
};

// Task Brief Templates API
export const taskBriefTemplatesAPI = {
  getAll: (params) => apiClient.get('/task-brief-templates', { params }),
  getById: (id) => apiClient.get(`/task-brief-templates/${id}`),
  create: (data) => apiClient.post('/task-brief-templates', data),
  update: (id, data) => apiClient.put(`/task-brief-templates/${id}`, data),
  delete: (id) => apiClient.delete(`/task-brief-templates/${id}`),
};

// Task Brief Questions API
export const taskBriefQuestionsAPI = {
  getAll: (params) => apiClient.get('/task-brief-questions', { params }),
  getById: (id) => apiClient.get(`/task-brief-questions/${id}`),
  create: (data) => apiClient.post('/task-brief-questions', data),
  update: (id, data) => apiClient.put(`/task-brief-questions/${id}`, data),
  delete: (id) => apiClient.delete(`/task-brief-questions/${id}`),
};

// Task Brief Checklists API
export const taskBriefChecklistsAPI = {
  getAll: (params) => apiClient.get('/task-brief-checklists', { params }),
  getById: (id) => apiClient.get(`/task-brief-checklists/${id}`),
  create: (data) => apiClient.post('/task-brief-checklists', data),
  update: (id, data) => apiClient.put(`/task-brief-checklists/${id}`, data),
  delete: (id) => apiClient.delete(`/task-brief-checklists/${id}`),
};

// Notifications API
export const notificationsAPI = {
  getAll: (params) => apiClient.get('/notifications', { params }),
  markAsRead: (id) => apiClient.put(`/notifications/${id}/read`),
  markAllAsRead: () => apiClient.put('/notifications/read-all'),
};

// Profile API
export const profileAPI = {
  getById: (id) => apiClient.get(`/profile/${id}`),
  update: (id, data) => apiClient.put(`/profile/${id}`, data),
  updatePhoto: (id, photo) => {
    const formData = new FormData();
    formData.append('photo', photo);
    return apiClient.put(`/profile/${id}/photo`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
  },
};

// Dashboard API
export const dashboardAPI = {
  getData: () => apiClient.get('/dashboard'),
};

export default apiClient;
