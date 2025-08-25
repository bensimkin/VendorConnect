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
  fetchNotifications: () => Promise<void>;
  markAsRead: (id: number) => Promise<void>;
  markAllAsRead: () => Promise<void>;
  markAsUnread: (id: number) => Promise<void>;
  deleteNotification: (id: number) => Promise<void>;
  deleteReadNotifications: () => Promise<void>;
  fetchUnreadCount: () => Promise<void>;
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
  const { user } = useAuthStore();

  const fetchNotifications = async () => {
    if (!user) return;
    
    try {
      setLoading(true);
      const response = await apiClient.get('/notifications');
      const data = response.data.data?.data || response.data.data || [];
      setNotifications(Array.isArray(data) ? data : []);
    } catch (error) {
      console.error('Failed to fetch notifications:', error);
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
    }
  };

  const markAsRead = async (id: number) => {
    try {
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
    }
  };

  const markAllAsRead = async () => {
    try {
      await apiClient.post('/notifications/mark-all-read');
      setNotifications(prev => 
        prev.map(notification => ({ ...notification, read_at: new Date().toISOString() }))
      );
      await fetchUnreadCount();
    } catch (error) {
      console.error('Failed to mark all notifications as read:', error);
    }
  };

  const markAsUnread = async (id: number) => {
    try {
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
    }
  };

  const deleteNotification = async (id: number) => {
    try {
      await apiClient.delete(`/notifications/${id}`);
      setNotifications(prev => prev.filter(notification => notification.id !== id));
      await fetchUnreadCount();
    } catch (error) {
      console.error('Failed to delete notification:', error);
    }
  };

  const deleteReadNotifications = async () => {
    try {
      await apiClient.delete('/notifications/read');
      setNotifications(prev => prev.filter(notification => !notification.read_at));
    } catch (error) {
      console.error('Failed to delete read notifications:', error);
    }
  };

  // Initial fetch
  useEffect(() => {
    if (user) {
      fetchNotifications();
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
    fetchNotifications,
    markAsRead,
    markAllAsRead,
    markAsUnread,
    deleteNotification,
    deleteReadNotifications,
    fetchUnreadCount,
  };

  return (
    <NotificationContext.Provider value={value}>
      {children}
    </NotificationContext.Provider>
  );
};
