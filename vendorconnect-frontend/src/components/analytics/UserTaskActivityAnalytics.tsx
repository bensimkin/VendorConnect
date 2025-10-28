'use client';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Activity, Users, Clock, TrendingUp, AlertCircle, CheckCircle } from 'lucide-react';

interface UserTaskActivityAnalyticsProps {
  statistics: any;
}

export default function UserTaskActivityAnalytics({ statistics }: UserTaskActivityAnalyticsProps) {
  const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  return (
    <div className="space-y-6">
      {/* Overview Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Activities</CardTitle>
            <Activity className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.overview.total_activities}</div>
            <p className="text-xs text-muted-foreground">Task interactions</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Tasks With Activity</CardTitle>
            <CheckCircle className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.overview.unique_tasks_with_activity}</div>
            <p className="text-xs text-muted-foreground">Active tasks</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Active Users</CardTitle>
            <Users className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.overview.unique_users_with_activity}</div>
            <p className="text-xs text-muted-foreground">Engaged users</p>
          </CardContent>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Most Active Users */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Users className="w-5 h-5" />
              Most Active Users
            </CardTitle>
          </CardHeader>
          <CardContent className="max-h-[300px] overflow-y-auto">
            <div className="space-y-3">
              {statistics.users_by_activity_level.length > 0 ? (
                statistics.users_by_activity_level.map((user: any) => (
                  <div key={user.user_id} className="flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100">
                    <div className="flex-1">
                      <p className="font-medium text-sm">{user.user_name}</p>
                      <p className="text-xs text-gray-500">{user.user_email}</p>
                      <p className="text-xs text-gray-400 mt-1">
                        Last: {formatDate(user.most_recent_activity)}
                      </p>
                    </div>
                    <Badge variant="secondary">{user.active_tasks_count} tasks</Badge>
                  </div>
                ))
              ) : (
                <p className="text-center text-gray-500 py-4">No data available</p>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Most Active Tasks */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <TrendingUp className="w-5 h-5" />
              Most Active Tasks
            </CardTitle>
          </CardHeader>
          <CardContent className="max-h-[300px] overflow-y-auto">
            <div className="space-y-3">
              {statistics.most_active_tasks.length > 0 ? (
                statistics.most_active_tasks.map((task: any) => (
                  <div key={task.task_id} className="flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100">
                    <div className="flex-1">
                      <p className="font-medium text-sm">{task.task_title}</p>
                      <p className="text-xs text-gray-500">Task #{task.task_id}</p>
                      <p className="text-xs text-gray-400 mt-1">
                        Last activity: {formatDate(task.last_activity)}
                      </p>
                    </div>
                    <Badge variant="secondary">{task.active_users_count} users</Badge>
                  </div>
                ))
              ) : (
                <p className="text-center text-gray-500 py-4">No data available</p>
              )}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Tasks with No Activity Alert */}
      {statistics.tasks_with_no_activity.length > 0 && (
        <Card className="border-red-200">
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-red-700">
              <AlertCircle className="w-5 h-5" />
              Tasks with No Recent Activity
            </CardTitle>
            <p className="text-sm text-gray-600 mt-2">
              Tasks with no activity from assignees in the last 7 days
            </p>
          </CardHeader>
          <CardContent  className="max-h-[300px] overflow-y-auto">
            <div className="space-y-3">
              {statistics.tasks_with_no_activity.map((task: any) => (
                <div key={task.task_id} className="flex items-start justify-between p-4 rounded-lg bg-red-50 border border-red-200">
                  <div className="flex-1">
                    <p className="font-medium text-sm text-red-900">{task.task_title}</p>
                    <div className="flex flex-wrap gap-2 mt-2">
                      <p className="text-xs text-gray-600">
                        Assigned: {Array.isArray(task.assigned_users) ? task.assigned_users.join(', ') : task.assigned_users}
                      </p>
                      {task.status_title && (
                        <Badge variant="outline" className="text-xs">
                          {task.status_title}
                        </Badge>
                      )}
                      {task.priority_title && (
                        <Badge variant="outline" className="text-xs">
                          {task.priority_title}
                        </Badge>
                      )}
                    </div>
                    <p className="text-xs text-red-700 mt-1">
                      Days since activity: {task.days_since_activity}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Session Activity Trend */}
      {statistics.activity_trend && statistics.activity_trend.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <TrendingUp className="w-5 h-5" />
              Session Activity Trend
            </CardTitle>
          </CardHeader>
          <CardContent className="max-h-[300px] overflow-y-auto">
            <div className="space-y-3">
              {statistics.activity_trend.map((day: any) => (
                <div key={day.date} className="flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100">
                  <div className="flex-1">
                    <p className="font-medium text-sm">{day.date}</p>
                  </div>
                  <div className="flex items-center gap-4">
                    <div className="text-right">
                      <p className="text-sm font-bold">{day.active_tasks}</p>
                      <p className="text-xs text-gray-500">Active Tasks</p>
                    </div>
                    <div className="text-right">
                      <p className="text-sm font-bold">{day.active_users}</p>
                      <p className="text-xs text-gray-500">Active Users</p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}


