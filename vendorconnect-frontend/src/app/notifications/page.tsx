'use client';

import { useState, useEffect } from 'react';
import { Bell, Check, X, Trash2, Filter, RefreshCw } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { useNotifications } from '@/contexts/NotificationContext';
import { format } from 'date-fns';
import MainLayout from '@/components/layout/main-layout';

export default function NotificationsPage() {
  const {
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
    clearError,
  } = useNotifications();

  const [filters, setFilters] = useState({
    read: undefined as boolean | undefined,
    type: '',
    priority: '',
  });
  const [showFilters, setShowFilters] = useState(false);

  useEffect(() => {
    fetchNotifications(filters);
  }, [filters]);

  const handleFilterChange = (key: string, value: any) => {
    setFilters(prev => ({ ...prev, [key]: value }));
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'urgent':
        return 'bg-red-100 text-red-800 border-red-200';
      case 'high':
        return 'bg-orange-100 text-orange-800 border-orange-200';
      case 'medium':
        return 'bg-blue-100 text-blue-800 border-blue-200';
      case 'low':
        return 'bg-green-100 text-green-800 border-green-200';
      default:
        return 'bg-gray-100 text-gray-800 border-gray-200';
    }
  };

  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'task_assigned':
        return '📋';
      case 'task_completed':
        return '✅';
      case 'task_due_soon':
        return '⏰';
      case 'task_overdue':
        return '🚨';
      case 'deliverable_added':
        return '📎';
      case 'comment_added':
        return '💬';
      case 'project_updated':
        return '📁';
      case 'client_updated':
        return '👤';
      default:
        return '🔔';
    }
  };

  const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60));
    
    if (diffInMinutes < 1) return 'Just now';
    if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
    if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
    return format(date, 'MMM dd, yyyy');
  };

  if (loading && notifications.length === 0) {
    return (
      <MainLayout>
        <div className="min-h-screen bg-gray-50 flex items-center justify-center">
          <div className="text-center">
            <RefreshCw className="h-8 w-8 animate-spin mx-auto mb-4 text-blue-600" />
            <p className="text-gray-600">Loading notifications...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="min-h-screen bg-gray-50">
        <div className="max-w-4xl mx-auto px-4 py-8">
        {/* Header */}
        <Card className="mb-6">
          <CardHeader>
            <div className="flex items-center justify-between  flex-wrap gap-2">
              <div className="flex items-center space-x-3">
                <Bell className="h-8 w-8 text-blue-600" />
                <div>
                  <CardTitle>Notifications</CardTitle>
                  <p className="text-gray-600">
                    {unreadCount} unread notification{unreadCount !== 1 ? 's' : ''}
                  </p>
                </div>
              </div>
              <div className="flex items-center space-x-2 flex-wrap gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setShowFilters(!showFilters)}
                >
                  <Filter className="h-4 w-4 mr-2" />
                  Filters
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => fetchNotifications(filters)}
                >
                  <RefreshCw className="h-4 w-4 mr-2" />
                  Refresh
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={markAllAsRead}
                >
                  <Check className="h-4 w-4 mr-2" />
                  Mark All Read
                </Button>
              </div>
            </div>

            {/* Filters */}
            {showFilters && (
              <div className="mt-4 pt-4 border-t">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Read Status
                    </label>
                    <select
                      value={filters.read === undefined ? '' : filters.read.toString()}
                      onChange={(e) => handleFilterChange('read', e.target.value === '' ? undefined : e.target.value === 'true')}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">All</option>
                      <option value="false">Unread</option>
                      <option value="true">Read</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Type
                    </label>
                    <select
                      value={filters.type}
                      onChange={(e) => handleFilterChange('type', e.target.value)}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">All Types</option>
                      <option value="task_assigned">Task Assigned</option>
                      <option value="task_completed">Task Completed</option>
                      <option value="task_due_soon">Task Due Soon</option>
                      <option value="task_overdue">Task Overdue</option>
                      <option value="deliverable_added">Deliverable Added</option>
                      <option value="comment_added">Comment Added</option>
                      <option value="project_updated">Project Updated</option>
                      <option value="client_updated">Client Updated</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Priority
                    </label>
                    <select
                      value={filters.priority}
                      onChange={(e) => handleFilterChange('priority', e.target.value)}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">All Priorities</option>
                      <option value="urgent">Urgent</option>
                      <option value="high">High</option>
                      <option value="medium">Medium</option>
                      <option value="low">Low</option>
                    </select>
                  </div>
                </div>
              </div>
            )}
          </CardHeader>
        </Card>

        {/* Error Message */}
        {error && (
          <Card className="mb-6 border-red-200 bg-red-50">
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div className="flex items-center">
                  <X className="h-5 w-5 text-red-400 mr-2" />
                  <p className="text-red-800">{error}</p>
                </div>
                <Button variant="ghost" size="sm" onClick={clearError}>
                  Dismiss
                </Button>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Notifications List */}
        <div className="space-y-4">
          {notifications.length === 0 ? (
            <Card>
              <CardContent className="p-8 text-center">
                <Bell className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">No notifications</h3>
                <p className="text-gray-600">You're all caught up! No notifications to show.</p>
              </CardContent>
            </Card>
          ) : (
            notifications.map((notification) => (
              <Card
                key={notification.id}
                className={`transition-all duration-200 hover:shadow-md ${
                  !notification.read_at ? 'border-l-4 border-l-blue-500' : ''
                }`}
              >
                <CardContent className="p-6">
                  <div className="flex items-start justify-between flex-wrap gap-2">
                    <div className="flex items-start space-x-3 flex-1">
                      <div className="text-2xl">
                        {getTypeIcon(notification.type)}
                      </div>
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center space-x-2 mb-2">
                          <h3 className={`text-lg font-medium ${
                            !notification.read_at ? 'text-gray-900' : 'text-gray-700'
                          }`}>
                            {notification.title}
                          </h3>
                          <Badge className={getPriorityColor(notification.priority)}>
                            {notification.priority}
                          </Badge>
                        </div>
                        <p className={`text-sm ${
                          !notification.read_at ? 'text-gray-900' : 'text-gray-600'
                        }`}>
                          {notification.message}
                        </p>
                        <div className="flex items-center space-x-4 mt-3 text-xs text-gray-500">
                          <span>
                            {formatTime(notification.created_at)}
                          </span>
                          <span className="capitalize">
                            {notification.type.replace('_', ' ')}
                          </span>
                        </div>
                      </div>
                    </div>
                    <div className="flex items-center space-x-2 ml-4 flex-wrap gap-2">
                      {notification.action_url && (
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => window.open(notification.action_url, '_blank')}
                        >
                          View Details
                        </Button>
                      )}
                      {!notification.read_at ? (
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => markAsRead(notification.id)}
                          className="text-green-600 hover:text-green-700"
                        >
                          <Check className="h-4 w-4 mr-1" />
                          Mark Read
                        </Button>
                      ) : (
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => markAsUnread(notification.id)}
                          className="text-blue-600 hover:text-blue-700"
                        >
                          <X className="h-4 w-4 mr-1" />
                          Mark Unread
                        </Button>
                      )}
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => deleteNotification(notification.id)}
                        className="text-red-600 hover:text-red-700"
                      >
                        <Trash2 className="h-4 w-4 mr-1" />
                        Delete
                      </Button>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))
          )}
        </div>

        {/* Bulk Actions */}
        {notifications.length > 0 && (
          <Card className="mt-6">
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <p className="text-sm text-gray-600">
                  {notifications.length} notification{notifications.length !== 1 ? 's' : ''} total
                </p>
                <div className="flex space-x-2">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={deleteReadNotifications}
                  >
                    <Trash2 className="h-4 w-4 mr-2" />
                    Delete Read
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        )}
        </div>
      </div>
    </MainLayout>
  );
}
