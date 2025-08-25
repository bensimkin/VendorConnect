'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import apiClient from '@/lib/api-client';
import { Activity, CheckCircle2, Clock, TrendingUp, AlertCircle, FileText, Calendar, User, Plus, FolderOpen } from 'lucide-react';
import { format } from 'date-fns';

interface RequesterDashboardData {
  overview: {
    total_tasks: number;
    completed_tasks: number;
    pending_tasks: number;
    overdue_tasks: number;
    total_projects: number;
  };
  recent_tasks: Array<{
    id: number;
    title: string;
    status?: { name: string; color?: string };
    priority?: { name: string; color?: string };
    end_date?: string;
    project?: { title: string };
    users?: Array<{ first_name: string; last_name: string }>;
    created_at: string;
  }>;
  recent_projects: Array<{
    id: number;
    title: string;
    status?: { title: string };
    total_tasks: number;
    completed_tasks: number;
    created_at: string;
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
    created_by: { first_name: string; last_name: string };
    created_at: string;
  }>;
}

export default function RequesterDashboardPage() {
  const router = useRouter();
  const [data, setData] = useState<RequesterDashboardData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchRequesterDashboardData();
  }, []);

  const fetchRequesterDashboardData = async () => {
    try {
      const response = await apiClient.get('/dashboard/requester');
      setData(response.data.data);
    } catch (error) {
      console.error('Failed to fetch requester dashboard data:', error);
      // Set default data structure to prevent errors
      setData({
        overview: {
          total_tasks: 0,
          completed_tasks: 0,
          pending_tasks: 0,
          overdue_tasks: 0,
          total_projects: 0,
        },
        recent_tasks: [],
        recent_projects: [],
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

  const calculateProgress = (completed: number, total: number) => {
    if (total === 0) return 0;
    return Math.round((completed / total) * 100);
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
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold">My Dashboard</h1>
            <p className="text-muted-foreground">
              Overview of your projects and tasks
            </p>
          </div>
          <div className="flex gap-2">
            <Button onClick={() => router.push('/tasks/new')}>
              <Plus className="h-4 w-4 mr-2" />
              New Task
            </Button>
            <Button variant="outline" onClick={() => router.push('/projects/new')}>
              <FolderOpen className="h-4 w-4 mr-2" />
              New Project
            </Button>
          </div>
        </div>

        {/* Overview Cards */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Tasks</CardTitle>
              <Activity className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{data?.overview.total_tasks || 0}</div>
              <p className="text-xs text-muted-foreground">
                Created by you
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

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Projects</CardTitle>
              <FolderOpen className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{data?.overview.total_projects || 0}</div>
              <p className="text-xs text-muted-foreground">
                Active projects
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
                  <CardDescription>Your latest created tasks</CardDescription>
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
                              {task.status.name}
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
                          {task.users && task.users.length > 0 && (
                            <span className="flex items-center gap-1">
                              <User className="h-3 w-3" />
                              {task.users.map(u => `${u.first_name} ${u.last_name}`).join(', ')}
                            </span>
                          )}
                          {task.end_date && (
                            <span className="flex items-center gap-1">
                              <Calendar className="h-3 w-3" />
                              Due {formatDate(task.end_date)}
                            </span>
                          )}
                        </div>
                      </div>
                      {task.priority && (
                        <div className={`w-2 h-2 rounded-full ${getPriorityColor(task.priority.name)}`} />
                      )}
                    </div>
                  ))
                ) : (
                  <p className="text-center text-muted-foreground py-4">No tasks created yet</p>
                )}
              </div>
            </CardContent>
          </Card>

          {/* Recent Projects */}
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <div>
                  <CardTitle>Recent Projects</CardTitle>
                  <CardDescription>Your latest projects</CardDescription>
                </div>
                <Button variant="outline" size="sm" onClick={() => router.push('/projects')}>
                  View All
                </Button>
              </div>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {data?.recent_projects && data.recent_projects.length > 0 ? (
                  data.recent_projects.slice(0, 5).map((project) => (
                    <div key={project.id} className="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 cursor-pointer" onClick={() => router.push(`/projects/${project.id}`)}>
                      <div className="flex-1">
                        <div className="flex items-center gap-2 mb-1">
                          <h4 className="font-medium text-sm">{project.title}</h4>
                          {project.status && (
                            <Badge variant="secondary" className="text-xs">
                              {project.status.title}
                            </Badge>
                          )}
                        </div>
                        <div className="flex items-center gap-4 text-xs text-muted-foreground">
                          <span>{project.total_tasks} total tasks</span>
                          <span>{project.completed_tasks} completed</span>
                          <span className="font-medium">
                            {calculateProgress(project.completed_tasks, project.total_tasks)}% complete
                          </span>
                        </div>
                        <div className="w-full bg-gray-200 rounded-full h-1 mt-2">
                          <div 
                            className="bg-blue-600 h-1 rounded-full" 
                            style={{ width: `${calculateProgress(project.completed_tasks, project.total_tasks)}%` }}
                          ></div>
                        </div>
                      </div>
                    </div>
                  ))
                ) : (
                  <p className="text-center text-muted-foreground py-4">No projects created yet</p>
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
                <CardDescription>Latest deliverables from your tasks</CardDescription>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {data?.recent_deliverables && data.recent_deliverables.length > 0 ? (
                data.recent_deliverables.slice(0, 5).map((deliverable) => (
                  <div key={deliverable.id} className="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
                    <div className="flex-1">
                      <h4 className="font-medium text-sm">{deliverable.title}</h4>
                      <p className="text-xs text-muted-foreground">
                        For task: {deliverable.task_title}
                      </p>
                      <p className="text-xs text-muted-foreground">
                        By {deliverable.created_by.first_name} {deliverable.created_by.last_name} • {formatDate(deliverable.created_at)}
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
            <div className="grid gap-4 md:grid-cols-4">
              <Button 
                variant="outline" 
                className="h-20 flex-col gap-2"
                onClick={() => router.push('/tasks/new')}
              >
                <Plus className="h-6 w-6" />
                <span>Create Task</span>
              </Button>
              <Button 
                variant="outline" 
                className="h-20 flex-col gap-2"
                onClick={() => router.push('/projects/new')}
              >
                <FolderOpen className="h-6 w-6" />
                <span>Create Project</span>
              </Button>
              <Button 
                variant="outline" 
                className="h-20 flex-col gap-2"
                onClick={() => router.push('/tasks')}
              >
                <FileText className="h-6 w-6" />
                <span>View Tasks</span>
              </Button>
              <Button 
                variant="outline" 
                className="h-20 flex-col gap-2"
                onClick={() => router.push('/projects')}
              >
                <FolderOpen className="h-6 w-6" />
                <span>View Projects</span>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}
