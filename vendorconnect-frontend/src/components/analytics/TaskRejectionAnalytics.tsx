'use client';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, XCircle, FileX, Users } from 'lucide-react';

interface TaskRejectionAnalyticsProps {
  statistics: any;
}

export default function TaskRejectionAnalytics({ statistics }: TaskRejectionAnalyticsProps) {
  return (
    <div className="space-y-6">
      {/* Overview Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Rejections</CardTitle>
            <XCircle className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.overview.total_rejections}</div>
            <p className="text-xs text-muted-foreground">Task rejections</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Unique Tasks Rejected</CardTitle>
            <FileX className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.overview.unique_tasks_rejected}</div>
            <p className="text-xs text-muted-foreground">Distinct tasks</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Users Rejecting</CardTitle>
            <Users className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.overview.unique_users_rejecting}</div>
            <p className="text-xs text-muted-foreground">Who rejected tasks</p>
          </CardContent>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Top Rejecting Users */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Users className="w-5 h-5" />
              Top Rejecting Users
            </CardTitle>
          </CardHeader>
          <CardContent className="max-h-[300px] overflow-y-auto">
            <div className="space-y-3">
              {statistics.top_rejecting_users.length > 0 ? (
                statistics.top_rejecting_users.map((user: any) => (
                  <div key={user.user_id} className="flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100">
                    <div className="flex-1">
                      <p className="font-medium text-sm">{user.user_name}</p>
                      <p className="text-xs text-gray-500">{user.user_email}</p>
                    </div>
                    <Badge variant="destructive">{user.rejection_count} rejections</Badge>
                  </div>
                ))
              ) : (
                <p className="text-center text-gray-500 py-4">No data available</p>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Most Rejected Tasks */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <FileX className="w-5 h-5" />
              Most Rejected Tasks
            </CardTitle>
          </CardHeader>
          <CardContent className="max-h-[300px] overflow-y-auto">
            <div className="space-y-3">
              {statistics.most_rejected_tasks.length > 0 ? (
                statistics.most_rejected_tasks.map((task: any) => (
                  <div key={task.task_id} className="flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100">
                    <div className="flex-1">
                      <p className="font-medium text-sm">{task.task_title}</p>
                      <p className="text-xs text-gray-500">
                        Task #{task.task_id}
                        {task.task_type && ` • ${task.task_type}`}
                      </p>
                    </div>
                    <Badge variant="destructive">{task.rejection_count} rejections</Badge>
                  </div>
                ))
              ) : (
                <p className="text-center text-gray-500 py-4">No data available</p>
              )}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Recent Rejections */}
      {statistics.recent_rejections.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <AlertTriangle className="w-5 h-5" />
              Recent Rejections
            </CardTitle>
          </CardHeader>
          <CardContent className="max-h-[300px] overflow-y-auto">
            <div className="space-y-3">
              {statistics.recent_rejections.map((rejection: any) => (
                <div key={rejection.rejection_id} className="p-4 rounded-lg bg-orange-50 border border-orange-200">
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <p className="font-medium text-sm text-orange-900">{rejection.task_title}</p>
                      <p className="text-xs text-gray-600 mt-1">
                        Rejected by: {rejection.user_name} ({rejection.user_email})
                      </p>
                      {rejection.reason && (
                        <p className="text-xs text-gray-500 mt-1 italic">
                          Reason: {rejection.reason}
                        </p>
                      )}
                      <p className="text-xs text-orange-700 mt-1">
                        {new Date(rejection.rejected_at).toLocaleString()}
                      </p>
                    </div>
                    <Badge variant="outline">Task #{rejection.task_id}</Badge>
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

