'use client';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Eye, BarChart3, Users, Clock, TrendingUp, AlertCircle } from 'lucide-react';

interface TaskViewAnalyticsProps {
  statistics: any;
  formatDuration: (seconds: number | null) => string;
}

export default function TaskViewAnalytics({ statistics, formatDuration }: TaskViewAnalyticsProps) {
  return (
    <div className="space-y-6">
      {/* Overview Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Views</CardTitle>
            <Eye className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.overview.total_views}</div>
            <p className="text-xs text-muted-foreground">All task views</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Unique Tasks Viewed</CardTitle>
            <BarChart3 className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.overview.unique_tasks_viewed}</div>
            <p className="text-xs text-muted-foreground">Distinct tasks</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Active Users</CardTitle>
            <Users className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.overview.unique_users}</div>
            <p className="text-xs text-muted-foreground">Who viewed tasks</p>
          </CardContent>
        </Card>

        {/* <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Avg. View Duration</CardTitle>
            <Clock className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {formatDuration(statistics.overview.average_view_duration_seconds)}
            </div>
            <p className="text-xs text-muted-foreground">Per task view</p>
          </CardContent>
        </Card> */}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Most Viewed Tasks */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <TrendingUp className="w-5 h-5" />
              Most Viewed Tasks
            </CardTitle>
          </CardHeader>
          <CardContent className="max-h-[300px] overflow-y-auto">
            <div className="space-y-3">
              {statistics.most_viewed_tasks.length > 0 ? (
                statistics.most_viewed_tasks.map((task: any) => (
                  <div key={task.task_id} className="flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100">
                    <div className="flex-1">
                      <p className="font-medium text-sm">{task.task_title}</p>
                      <p className="text-xs text-gray-500">Task #{task.task_id}</p>
                    </div>
                    <Badge variant="secondary">{task.view_count} views</Badge>
                  </div>
                ))
              ) : (
                <p className="text-center text-gray-500 py-4">No data available</p>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Top Users */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Users className="w-5 h-5" />
              Top Users by Views
            </CardTitle>
          </CardHeader>
          <CardContent className="max-h-[300px] overflow-y-auto">
            <div className="space-y-3">
              {statistics.top_users.length > 0 ? (
                statistics.top_users.map((user: any) => (
                  <div key={user.user_id} className="flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100">
                    <div className="flex-1">
                      <p className="font-medium text-sm">{user.user_name}</p>
                      <p className="text-xs text-gray-500">{user.user_email}</p>
                    </div>
                    <Badge variant="secondary">{user.view_count} views</Badge>
                  </div>
                ))
              ) : (
                <p className="text-center text-gray-500 py-4">No data available</p>
              )}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Unviewed Tasks Alert */}
      {statistics.unviewed_tasks.length > 0 && (
        <Card className="border-orange-200">
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-orange-700">
              <AlertCircle className="w-5 h-5" />
              Unviewed Tasks Alert
            </CardTitle>
            <p className="text-sm text-gray-600 mt-2">
              Tasks assigned 3+ days ago but never viewed by assignees
            </p>
          </CardHeader>
          <CardContent className="max-h-[300px] overflow-y-auto">
            <div className="space-y-3">
              {statistics.unviewed_tasks.map((task: any) => (
                <div key={task.task_id} className="flex items-start justify-between p-4 rounded-lg bg-orange-50 border border-orange-200">
                  <div className="flex-1">
                    <p className="font-medium text-sm text-orange-900">{task.task_title}</p>
                    <div className="flex flex-wrap gap-2 mt-2">
                      <p className="text-xs text-gray-600">
                        Assigned: {task.assigned_users.join(', ')}
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
                    <p className="text-xs text-orange-700 mt-1">
                      Days since assignment: {task.days_since_assignment}
                    </p>
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

