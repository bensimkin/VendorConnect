'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import apiClient from '@/lib/api-client';
import { Activity, CheckCircle2, Clock, TrendingUp, AlertCircle, FileText, Calendar, User } from 'lucide-react';
import { format } from 'date-fns';

interface TaskerDashboardData {
  overview: {
    total_tasks: number;
    completed_tasks: number;
    pending_tasks: number;
    overdue_tasks: number;
  };
  recent_tasks: Array<{
    id: number;
    title: string;
    description?: string;
    note?: string;
    deliverable_quantity?: number;
      status?: { title: string };  // FIXED: Use primary database field
  priority?: { title: string };  // FIXED: Use primary database field
    end_date?: string;
    project?: { title: string };
    template?: {
      id: number;
      title: string;
      standard_brief?: string;
      description?: string;
      deliverable_quantity?: number;
    };
    created_at: string;
  }>;
  upcoming_deadlines: Array<{
    id: number;
    title: string;
    end_date: string;
    days_until_due: number;
    priority?: { title: string };  // FIXED: Use primary database field
  }>;
  task_statistics: {
    by_status: Record<string, number>;
    completed_this_week: number;
    overdue: number;
  };
  recent_deliverables: Array<{
    id: number;
    title: string;
    task_title: string;
    created_at: string;
  }>;
}

export default function TaskerDashboardPage() {
  const router = useRouter();
  const [data, setData] = useState<TaskerDashboardData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchTaskerDashboardData();
  }, []);

  const fetchTaskerDashboardData = async () => {
    try {
      const response = await apiClient.get('/dashboard/tasker');
      setData(response.data.data);
    } catch (error) {
      console.error('Failed to fetch tasker dashboard data:', error);
      // Set default data structure to prevent errors
      setData({
        overview: {
          total_tasks: 0,
          completed_tasks: 0,
          pending_tasks: 0,
          overdue_tasks: 0,
        },
        recent_tasks: [],
        upcoming_deadlines: [],
        task_statistics: {
          by_status: {},
          completed_this_week: 0,
          overdue: 0,
        },
        recent_deliverables: [],
      });
    } finally {
      setLoading(false);
    }
  };

  const getStatusColor = (statusName?: string) => {
    switch (statusName?.toLowerCase()) {
      case 'completed':
        return 'bg-green-500';
      case 'in progress':
        return 'bg-blue-500';
      case 'pending':
        return 'bg-yellow-500';
      case 'overdue':
        return 'bg-red-500';
      default:
        return 'bg-gray-500';
    }
  };

  const getPriorityColor = (priorityName?: string) => {
    switch (priorityName?.toLowerCase()) {
      case 'urgent':
        return 'bg-red-500';
      case 'high':
        return 'bg-orange-500';
      case 'medium':
        return 'bg-blue-500';
      case 'low':
        return 'bg-gray-500';
      default:
        return 'bg-gray-500';
    }
  };

  const formatDate = (dateString: string) => {
    try {
      return format(new Date(dateString), 'MMM dd, yyyy');
    } catch {
      return 'Invalid date';
    }
  };

  const getDaysUntilDue = (endDate: string) => {
    const today = new Date();
    const dueDate = new Date(endDate);
    const diffTime = dueDate.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading your dashboard...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="space-y-6">
        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold">My Dashboard</h1>
          <p className="text-muted-foreground">
            Overview of your assigned tasks and progress
          </p>
        </div>

        {/* Overview Cards */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Tasks</CardTitle>
              <Activity className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{data?.overview.total_tasks || 0}</div>
              <p className="text-xs text-muted-foreground">
                Assigned to you
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Completed</CardTitle>
              <CheckCircle2 className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{data?.overview.completed_tasks || 0}</div>
              <p className="text-xs text-muted-foreground">
                Successfully delivered
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pending</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{data?.overview.pending_tasks || 0}</div>
              <p className="text-xs text-muted-foreground">
                Awaiting completion
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Overdue</CardTitle>
              <AlertCircle className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-red-600">{data?.overview.overdue_tasks || 0}</div>
              <p className="text-xs text-muted-foreground">
                Past deadline
              </p>
            </CardContent>
          </Card>
        </div>

        <div className="grid gap-6 md:grid-cols-2">
          {/* Recent Tasks */}
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <div>
                  <CardTitle>Recent Tasks</CardTitle>
                  <CardDescription>Your latest assigned tasks</CardDescription>
                </div>
                <Button variant="outline" size="sm" onClick={() => router.push('/tasks')}>
                  View All
                </Button>
              </div>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {data?.recent_tasks && data.recent_tasks.length > 0 ? (
                  data.recent_tasks.slice(0, 5).map((task) => (
                    <div key={task.id} className="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 cursor-pointer" onClick={() => router.push(`/tasks/${task.id}`)}>
                      <div className="flex-1">
                        <div className="flex items-center gap-2 mb-1">
                          <h4 className="font-medium text-sm">{task.title}</h4>
                          {task.status && (
                            <Badge variant="secondary" className="text-xs">
                              {task.status.title}
                            </Badge>
                          )}
                        </div>
                        <div className="flex items-center gap-4 text-xs text-muted-foreground">
                          {task.project && (
                            <span className="flex items-center gap-1">
                              <FileText className="h-3 w-3" />
                              {task.project.title}
                            </span>
                          )}
                          {task.end_date && (
                            <span className="flex items-center gap-1">
                              <Calendar className="h-3 w-3" />
                              Due {formatDate(task.end_date)}
                            </span>
                          )}
                          {task.template && (
                            <span className="flex items-center gap-1 text-blue-600">
                              <span className="text-xs bg-blue-100 text-blue-800 px-1 py-0.5 rounded">
                                {task.template.title}
                              </span>
                            </span>
                          )}
                        </div>
                        {task.note && (
                          <div className="text-xs text-muted-foreground line-clamp-1 mt-1">
                            <span className="font-medium">Notes:</span> {task.note}
                          </div>
                        )}
                        {task.deliverable_quantity && task.deliverable_quantity > 1 && (
                          <div className="text-xs text-green-600 mt-1">
                            <span className="bg-green-100 text-green-800 px-1 py-0.5 rounded">
                              Qty: {task.deliverable_quantity}
                            </span>
                          </div>
                        )}
                      </div>
                      {task.priority && (
                        <div className={`w-2 h-2 rounded-full ${getPriorityColor(task.priority.title)}`} />
                      )}
                    </div>
                  ))
                ) : (
                  <p className="text-center text-muted-foreground py-4">No tasks assigned yet</p>
                )}
              </div>
            </CardContent>
          </Card>

          {/* Upcoming Deadlines */}
          <Card>
            <CardHeader>
              <CardTitle>Upcoming Deadlines</CardTitle>
              <CardDescription>Tasks due in the next 7 days</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {data?.upcoming_deadlines && data.upcoming_deadlines.length > 0 ? (
                  data.upcoming_deadlines.slice(0, 5).map((task) => (
                    <div key={task.id} className="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 cursor-pointer" onClick={() => router.push(`/tasks/${task.id}`)}>
                      <div className="flex-1">
                        <h4 className="font-medium text-sm">{task.title}</h4>
                        <div className="flex items-center gap-4 text-xs text-muted-foreground mt-1">
                          <span>Due {formatDate(task.end_date)}</span>
                          <span className={`font-medium ${task.days_until_due <= 1 ? 'text-red-600' : task.days_until_due <= 3 ? 'text-orange-600' : 'text-green-600'}`}>
                            {task.days_until_due === 0 ? 'Today' : 
                             task.days_until_due === 1 ? 'Tomorrow' : 
                             `${task.days_until_due} days`}
                          </span>
                        </div>
                      </div>
                      {task.priority && (
                        <Badge variant="secondary" className="text-xs">
                          {task.priority.title}
                        </Badge>
                      )}
                    </div>
                  ))
                ) : (
                  <p className="text-center text-muted-foreground py-4">No upcoming deadlines</p>
                )}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Recent Deliverables */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <div>
                <CardTitle>Recent Deliverables</CardTitle>
                <CardDescription>Your latest uploaded deliverables</CardDescription>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {data?.recent_deliverables && data.recent_deliverables.length > 0 ? (
                data.recent_deliverables.slice(0, 5).map((deliverable) => (
                  <div key={deliverable.id} className="flex items-center justify-between p-3 border rounded-lg">
                    <div className="flex-1">
                      <h4 className="font-medium text-sm">{deliverable.title}</h4>
                      <p className="text-xs text-muted-foreground">
                        For task: {deliverable.task_title}
                      </p>
                      <p className="text-xs text-muted-foreground">
                        Uploaded {formatDate(deliverable.created_at)}
                      </p>
                    </div>
                  </div>
                ))
              ) : (
                <p className="text-center text-muted-foreground py-4">No recent deliverables</p>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Quick Actions */}
        <Card>
          <CardHeader>
            <CardTitle>Quick Actions</CardTitle>
            <CardDescription>Common tasks and shortcuts</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 md:grid-cols-1">
              <Button 
                variant="outline" 
                className="h-20 flex-col gap-2"
                onClick={() => router.push('/tasks')}
              >
                <FileText className="h-6 w-6" />
                <span>View My Tasks</span>
              </Button>


            </div>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}
