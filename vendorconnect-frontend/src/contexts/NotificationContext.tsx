'use client';

import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import apiClient from '@/lib/api-client';
import { useAuthStore } from '@/lib/auth-store';

interface Notification {
  id: number;
  type: string;
  title: string;
  message: string;
  data?: any;
  read_at?: string;
  action_url?: string;
  priority: 'low' | 'medium' | 'high' | 'urgent';
  created_at: string;
}

interface NotificationContextType {
  notifications: Notification[];
  unreadCount: number;
  loading: boolean;
  error: string | null;
  fetchNotifications: (filters?: { read?: boolean; type?: string; priority?: string }) => Promise<void>;
  markAsRead: (id: number) => Promise<void>;
  markAllAsRead: () => Promise<void>;
  markAsUnread: (id: number) => Promise<void>;
  deleteNotification: (id: number) => Promise<void>;
  deleteReadNotifications: () => Promise<void>;
  fetchUnreadCount: () => Promise<void>;
  clearError: () => void;
}

const NotificationContext = createContext<NotificationContextType | undefined>(undefined);

export const useNotifications = () => {
  const context = useContext(NotificationContext);
  if (context === undefined) {
    throw new Error('useNotifications must be used within a NotificationProvider');
  }
  return context;
};

interface NotificationProviderProps {
  children: ReactNode;
}

export const NotificationProvider: React.FC<NotificationProviderProps> = ({ children }) => {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const { user } = useAuthStore();

  const fetchNotifications = async (filters?: { read?: boolean; type?: string; priority?: string }) => {
    if (!user) return;
    
    try {
      setLoading(true);
      setError(null);
      
      const params = new URLSearchParams();
      if (filters?.read !== undefined) {
        params.append('read', filters.read.toString());
      }
      if (filters?.type) {
        params.append('type', filters.type);
      }
      if (filters?.priority) {
        params.append('priority', filters.priority);
      }
      
      const response = await apiClient.get(`/notifications?${params.toString()}`);
      // Handle paginated response structure
      const data = response.data.data?.data || response.data.data || [];
      setNotifications(Array.isArray(data) ? data : []);
    } catch (error) {
      console.error('Failed to fetch notifications:', error);
      setError('Failed to fetch notifications');
      setNotifications([]);
    } finally {
      setLoading(false);
    }
  };

  const fetchUnreadCount = async () => {
    if (!user) return;
    
    try {
      const response = await apiClient.get('/notifications/unread-count');
      setUnreadCount(response.data.data?.count || 0);
    } catch (error) {
      console.error('Failed to fetch unread count:', error);
      setError('Failed to fetch unread count');
    }
  };

  const markAsRead = async (id: number) => {
    try {
      setError(null);
      await apiClient.post(`/notifications/${id}/read`);
      setNotifications(prev => 
        prev.map(notification => 
          notification.id === id 
            ? { ...notification, read_at: new Date().toISOString() }
            : notification
        )
      );
      await fetchUnreadCount();
    } catch (error) {
      console.error('Failed to mark notification as read:', error);
      setError('Failed to mark notification as read');
    }
  };

  const markAllAsRead = async () => {
    try {
      setError(null);
      await apiClient.post('/notifications/mark-all-read');
      setNotifications(prev => 
        prev.map(notification => ({ ...notification, read_at: new Date().toISOString() }))
      );
      await fetchUnreadCount();
    } catch (error) {
      console.error('Failed to mark all notifications as read:', error);
      setError('Failed to mark all notifications as read');
    }
  };

  const markAsUnread = async (id: number) => {
    try {
      setError(null);
      await apiClient.post(`/notifications/${id}/unread`);
      setNotifications(prev => 
        prev.map(notification => 
          notification.id === id 
            ? { ...notification, read_at: undefined }
            : notification
        )
      );
      await fetchUnreadCount();
    } catch (error) {
      console.error('Failed to mark notification as unread:', error);
      setError('Failed to mark notification as unread');
    }
  };

  const deleteNotification = async (id: number) => {
    try {
      setError(null);
      await apiClient.delete(`/notifications/${id}`);
      setNotifications(prev => prev.filter(notification => notification.id !== id));
      await fetchUnreadCount();
    } catch (error) {
      console.error('Failed to delete notification:', error);
      setError('Failed to delete notification');
    }
  };

  const deleteReadNotifications = async () => {
    try {
      setError(null);
      await apiClient.delete('/notifications/read');
      setNotifications(prev => prev.filter(notification => !notification.read_at));
    } catch (error) {
      console.error('Failed to delete read notifications:', error);
      setError('Failed to delete read notifications');
    }
  };

  const clearError = () => {
    setError(null);
  };

  // Initial fetch - only get unread count, not full notifications
  useEffect(() => {
    if (user) {
      fetchUnreadCount();
    }
  }, [user]);

  // Poll for new notifications every 30 seconds
  useEffect(() => {
    if (!user) return;

    const interval = setInterval(() => {
      fetchUnreadCount();
    }, 30000);

    return () => clearInterval(interval);
  }, [user]);

  const value: NotificationContextType = {
    notifications,
    unreadCount,
    loading,
    error,
    fetchNotifications,
    markAsRead,
    markAllAsRead,
    markAsUnread,
    deleteNotification,
    deleteReadNotifications,
    fetchUnreadCount,
    clearError,
  };

  return (
    <NotificationContext.Provider value={value}>
      {children}
    </NotificationContext.Provider>
  );
};
