'use client';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { MessageSquare, TrendingUp, TrendingDown, Activity, Clock } from 'lucide-react';

interface CommentActivityStatistics {
  period: {
    start_date: string;
    end_date: string;
  };
  overview: {
    total_comments: number;
    unique_tasks_with_comments: number;
    unique_users_commenting: number;
    average_comments_per_task: number;
  };
  most_active_tasks: Array<{
    task_id: number;
    task_title: string;
    comment_count: number;
  }>;
  top_commenting_users: Array<{
    user_id: number;
    user_name: string;
    user_email: string;
    comment_count: number;
  }>;
  tasks_with_no_recent_comments: Array<{
    task_id: number;
    task_title: string;
    assigned_users: string[];
    status_title: string | null;
    priority_title: string | null;
  }>;
  recent_comments: Array<{
    comment_id: string;
    task_id: number;
    task_title: string;
    sender_id: number;
    sender_name: string;
    sender_email: string;
    comment_text: string;
    created_at: string;
  }>;
  activity_trend: Array<{
    date: string;
    total_comments: number;
    active_tasks: number;
    active_users: number;
  }>;
}

export default function CommentActivityAnalytics({ statistics }: { statistics: CommentActivityStatistics }) {
  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
  };

  const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString();
  };

  const { overview } = statistics;

  return (
    <div className="space-y-6">
      {/* Overview Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Total Comments</p>
                <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">{overview.total_comments}</p>
              </div>
              <MessageSquare className="w-8 h-8 text-blue-600" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Tasks with Comments</p>
                <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                  {overview.unique_tasks_with_comments}
                </p>
              </div>
              <Activity className="w-8 h-8 text-green-600" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Active Commenters</p>
                <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                  {overview.unique_users_commenting}
                </p>
              </div>
              <TrendingUp className="w-8 h-8 text-purple-600" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Avg per Task</p>
                <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                  {overview.average_comments_per_task.toFixed(1)}
                </p>
              </div>
              <TrendingDown className="w-8 h-8 text-orange-600" />
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Most Active Tasks */}
      {statistics.most_active_tasks.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <MessageSquare className="w-5 h-5" />
              Most Active Tasks (by Comments)
            </CardTitle>
          </CardHeader>
          <CardContent className="max-h-[300px] overflow-y-auto">
            <div className="space-y-2">
              {statistics.most_active_tasks.map((task) => (
                <div key={task.task_id} className="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                  <div className="flex-1">
                    <p className="font-medium text-gray-900 dark:text-gray-100">{task.task_title}</p>
                    <p className="text-sm text-gray-600 dark:text-gray-400">Task ID: {task.task_id}</p>
                  </div>
                  <div className="text-right">
                    <p className="text-2xl font-bold text-blue-600">{task.comment_count}</p>
                    <p className="text-xs text-gray-600 dark:text-gray-400">comments</p>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Top Commenting Users */}
      {statistics.top_commenting_users.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <TrendingUp className="w-5 h-5" />
              Top Commenting Users
            </CardTitle>
          </CardHeader>
          <CardContent className="max-h-[300px] overflow-y-auto">
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b">
                    <th className="text-left py-3 px-4">User</th>
                    <th className="text-left py-3 px-4">Email</th>
                    <th className="text-right py-3 px-4">Comments</th>
                  </tr>
                </thead>
                <tbody>
                  {statistics.top_commenting_users.map((user) => (
                    <tr key={user.user_id} className="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                      <td className="py-3 px-4 font-medium">{user.user_name}</td>
                      <td className="py-3 px-4 text-gray-600 dark:text-gray-400">{user.user_email}</td>
                      <td className="py-3 px-4 text-right font-semibold">{user.comment_count}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Tasks with No Recent Comments */}
      {statistics.tasks_with_no_recent_comments.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Clock className="w-5 h-5 text-orange-600" />
              Tasks with No Recent Comments (7+ days)
            </CardTitle>
          </CardHeader>
          <CardContent className="max-h-[300px] overflow-y-auto">
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b">
                    <th className="text-left py-3 px-4">Task</th>
                    <th className="text-left py-3 px-4">Assigned To</th>
                    <th className="text-left py-3 px-4">Status</th>
                    <th className="text-left py-3 px-4">Priority</th>
                  </tr>
                </thead>
                <tbody>
                  {statistics.tasks_with_no_recent_comments.map((task) => (
                    <tr key={task.task_id} className="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                      <td className="py-3 px-4 font-medium">{task.task_title}</td>
                      <td className="py-3 px-4 text-gray-600 dark:text-gray-400">
                        {task.assigned_users.join(', ')}
                      </td>
                      <td className="py-3 px-4">
                        <span className="px-2 py-1 rounded text-xs bg-gray-100 dark:bg-gray-800">
                          {task.status_title || 'N/A'}
                        </span>
                      </td>
                      <td className="py-3 px-4">
                        <span className="px-2 py-1 rounded text-xs bg-gray-100 dark:bg-gray-800">
                          {task.priority_title || 'N/A'}
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Recent Comments */}
      {statistics.recent_comments.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Clock className="w-5 h-5" />
              Recent Comments
            </CardTitle>
          </CardHeader>
          <CardContent className="max-h-[500px] overflow-y-auto">
            <div className="space-y-3">
              {statistics.recent_comments.map((comment) => (
                <div key={comment.comment_id} className="p-4 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                  <div className="flex items-start justify-between mb-2">
                    <div className="flex-1">
                      <p className="font-medium text-gray-900 dark:text-gray-100">{comment.sender_name}</p>
                      <p className="text-sm text-gray-600 dark:text-gray-400">{comment.sender_email}</p>
                    </div>
                    <div className="text-right text-sm text-gray-600 dark:text-gray-400">
                      <p>{formatDate(comment.created_at)}</p>
                      <p>{formatTime(comment.created_at)}</p>
                    </div>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300 mb-2">{comment.comment_text}</p>
                  <p className="text-xs text-gray-600 dark:text-gray-400">
                    On task: <span className="font-medium">{comment.task_title}</span>
                  </p>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Empty State */}
      {overview.total_comments === 0 && (
        <Card>
          <CardContent className="py-12">
            <div className="text-center">
              <MessageSquare className="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                No Comment Activity
              </h3>
              <p className="text-gray-600 dark:text-gray-400">
                No comments were made during this time period.
              </p>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}

