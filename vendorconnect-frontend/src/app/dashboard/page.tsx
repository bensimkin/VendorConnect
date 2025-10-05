'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useAuthStore } from '@/lib/auth-store';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import DateRangeSelector, { TimeRange } from '@/components/ui/date-range-selector';
import apiClient from '@/lib/api-client';
import { Activity, Users, CheckCircle2, Clock, TrendingUp, TrendingDown, XCircle, FileText } from 'lucide-react';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js';
import { Line, Bar, Doughnut } from 'react-chartjs-2';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
);

interface DashboardData {
  overview: {
    total_tasks: number;
    total_users: number;
    total_clients: number;
    total_projects: number;
  };
  task_statistics: {
    by_status: Record<string, number>;
    completed_this_week: number;
    overdue: number;
  };
  recent_tasks: Array<{
    id: number;
    title: string;
    description?: string;
    note?: string;
    deliverable_quantity?: number;
    deliverables_count?: number;
    status?: { title: string };  // FIXED: Use primary database field
    priority?: { title: string };  // FIXED: Use primary database field
    project?: { title: string };
    clients?: Array<{
      id: number;
      first_name?: string;
      last_name?: string;
      name?: string;
    }>;
    users?: Array<{
      id: number;
      first_name: string;
      last_name: string;
    }>;
    template?: {
      id: number;
      title: string;
      standard_brief?: string;
      description?: string;
      deliverable_quantity?: number;
    };
    created_at: string;
  }>;
  overdue_tasks: Array<{
    id: number;
    title: string;
    description?: string;
    end_date: string;
    status?: { id: number; title: string };
    priority?: { id: number; title: string };
    project?: { id: number; title: string };
    clients?: Array<{
      id: number;
      first_name?: string;
      last_name?: string;
      name?: string;
    }>;
    users?: Array<{
      id: number;
      first_name: string;
      last_name: string;
    }>;
    created_at: string;
  }>;
  user_activity: any[];
  task_trend: Array<{
    date: string;
    completed_tasks: number;
  }>;
  project_management?: Array<{
    id: number;
    title: string;
    status?: {
      id: number;
      title: string;
    };
    clients?: Array<{
      id: number;
      name: string;
    }>;
    total_tasks?: number;
    active_tasks?: number;
    overdue_tasks?: number;
    completed_this_week_tasks?: number;
    updated_at: string;
  }>;
  statuses?: Array<{
    id: number;
    title: string;
  }>;
  rejected_tasks_trend?: Array<{
    date: string;
    rejected_tasks: number;
  }>;
  tasks_with_unchecked_checklists?: {
    total_completed_with_checklists: number;
    completed_with_unchecked_items: number;
    percentage_with_unchecked: number;
    tasks_list: Array<{
      id: number;
      title: string;
      total_items: number;
      checked_items: number;
      unchecked_items: number;
      completion_date: string;
    }>;
  };
  average_task_completion_time?: {
    average_days: number;
    average_hours: number;
    total_completed_tasks: number;
    fastest_completion: number;
    slowest_completion: number;
  };
  additional_statistics?: {
    total_tasks: number;
    completed_tasks: number;
    rejected_tasks: number;
    pending_tasks: number;
    in_progress_tasks: number;
    completion_rate: number;
    rejection_rate: number;
    tasks_with_deliverables: number;
    deliverable_rate: number;
  };
}

export default function DashboardPage() {
  const router = useRouter();
  const { user } = useAuthStore();
  const [data, setData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);
  const [timeRange, setTimeRange] = useState<TimeRange>('all');

  // Redirect to role-specific dashboard
  useEffect(() => {
    if (user) {
      if (user.roles?.some(role => role.name === 'Tasker')) {
        router.replace('/dashboard/tasker');
        return;
      }
      if (user.roles?.some(role => role.name === 'Requester')) {
        router.replace('/dashboard/requester');
        return;
      }
    }
  }, [user, router]);

  useEffect(() => {
    fetchDashboardData();
    
    // Auto-refresh every 30 seconds
    const intervalId = setInterval(() => {
      fetchDashboardData();
    }, 30000);
    
    // Cleanup interval on unmount
    return () => clearInterval(intervalId);
  }, [timeRange]);

  const fetchDashboardData = async () => {
    try {
      setLoading(true);
      const response = await apiClient.get('/dashboard', {
        params: { time_range: timeRange }
      });
      console.log('Dashboard API response:', response.data.data);
      console.log('Recent tasks:', response.data.data.recent_tasks);
      if (response.data.data.recent_tasks && response.data.data.recent_tasks.length > 0) {
        console.log('First task:', response.data.data.recent_tasks[0]);
        console.log('First task title:', response.data.data.recent_tasks[0].title);
      }
      setData(response.data.data);
    } catch (error) {
      console.error('Failed to fetch dashboard data:', error);
      // Set default data structure to prevent errors
      setData({
        overview: {
          total_tasks: 0,
          total_users: 0,
          total_clients: 0,
          total_projects: 0,
        },
        task_statistics: {
          by_status: {},
          completed_this_week: 0,
          overdue: 0,
        },
        recent_tasks: [],
        overdue_tasks: [],
        user_activity: [],
        task_trend: [],
      });
    } finally {
      setLoading(false);
    }
  };

  // Calculate task completion stats from by_status
  const getTaskStats = () => {
    if (!data) return { completed: 0, pending: 0, inProgress: 0, submitted: 0 };
    
    const byStatus = data.task_statistics.by_status;
    const statuses = data.statuses || [];
    
    // Find status IDs by title
    const completedStatus = statuses.find(s => s.title === 'Completed');
    const pendingStatus = statuses.find(s => s.title === 'Pending');
    const inProgressStatus = statuses.find(s => s.title === 'In Progress');
    const submittedStatus = statuses.find(s => s.title === 'Submitted');
    
    return {
      completed: completedStatus ? (byStatus[completedStatus.id.toString()] || 0) : 0,
      pending: pendingStatus ? (byStatus[pendingStatus.id.toString()] || 0) : 0,
      inProgress: inProgressStatus ? (byStatus[inProgressStatus.id.toString()] || 0) : 0,
      submitted: submittedStatus ? (byStatus[submittedStatus.id.toString()] || 0) : 0,
    };
  };

  const taskStats = getTaskStats();

  const taskCompletionData = {
    labels: ['Completed', 'Pending', 'In Progress', 'Submitted'],
    datasets: [
      {
        data: [taskStats.completed, taskStats.pending, taskStats.inProgress, taskStats.submitted],
        backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#8b5cf6'],
        borderWidth: 0,
      },
    ],
  };

  const taskTrendData = {
    labels: data?.task_trend.map(t => new Date(t.date).toLocaleDateString('en', { weekday: 'short' })) || [],
    datasets: [
      {
        label: 'Tasks Completed',
        data: data?.task_trend.map(t => t.completed_tasks) || [],
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        tension: 0.4,
      },
    ],
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading dashboard...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  const overview = data?.overview || {
    total_tasks: 0,
    total_users: 0,
    total_clients: 0,
    total_projects: 0,
  };

  return (
    <MainLayout>
      <div className="space-y-8">
        <div className="flex items-center justify-between flex-wrap gap-2">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Dashboard</h1>
            <p className="text-muted-foreground">Welcome back! Here's what's happening with your projects.</p>
          </div>
          <DateRangeSelector 
            value={timeRange} 
            onChange={setTimeRange}
            className="hidden md:flex"
          />
        </div>
        
        {/* Mobile date range selector */}
        <div className="md:hidden">
          <DateRangeSelector 
            value={timeRange} 
            onChange={setTimeRange}
          />
        </div>

        {/* Stats Grid */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Tasks</CardTitle>
              <Activity className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{overview.total_tasks}</div>
              <p className="text-xs text-muted-foreground">
                <span className="text-green-600 inline-flex items-center">
                  <TrendingUp className="h-3 w-3 mr-1" />
                  {data?.task_statistics.completed_this_week || 0}
                </span>{' '}
                completed this week
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Completed Tasks</CardTitle>
              <CheckCircle2 className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{taskStats.completed}</div>
              <p className="text-xs text-muted-foreground">
                {overview.total_tasks > 0 ? Math.round((taskStats.completed / overview.total_tasks) * 100) : 0}% completion rate
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Active Users</CardTitle>
              <Users className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{overview.total_users}</div>
              <p className="text-xs text-muted-foreground">
                {data?.user_activity.length || 0} active this week
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Overdue Tasks</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{data?.task_statistics.overdue || 0}</div>
              <p className="text-xs text-muted-foreground">
                Needs immediate attention
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Charts */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
          <Card className="col-span-4">
            <CardHeader>
              <CardTitle>Task Completion Trend</CardTitle>
              <CardDescription>Daily completed tasks over the last week</CardDescription>
            </CardHeader>
            <CardContent>
              {data?.task_trend && data.task_trend.length > 0 ? (
                <Line 
                  data={taskTrendData} 
                  options={{
                    responsive: true,
                    plugins: {
                      legend: {
                        display: false,
                      },
                    },
                    scales: {
                      y: {
                        beginAtZero: true,
                      },
                    },
                  }}
                />
              ) : (
                <div className="flex items-center justify-center h-64 text-muted-foreground">
                  No trend data available
                </div>
              )}
            </CardContent>
          </Card>

          <Card className="col-span-3">
            <CardHeader>
              <CardTitle>Task Status</CardTitle>
              <CardDescription>Current task distribution</CardDescription>
            </CardHeader>
            <CardContent>
              {overview.total_tasks > 0 ? (
                <Doughnut 
                  data={taskCompletionData}
                  options={{
                    responsive: true,
                    plugins: {
                      legend: {
                        position: 'bottom',
                      },
                    },
                  }}
                />
              ) : (
                <div className="flex items-center justify-center h-64 text-muted-foreground">
                  No tasks to display
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        {/* New Statistics Section */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          {/* Average Completion Time */}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Avg. Completion Time</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {data?.average_task_completion_time?.average_days || 0}d
              </div>
              <p className="text-xs text-muted-foreground">
                {data?.average_task_completion_time?.average_hours || 0}h average
              </p>
            </CardContent>
          </Card>

          {/* Completion Rate */}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Completion Rate</CardTitle>
              <TrendingUp className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {data?.additional_statistics?.completion_rate || 0}%
              </div>
              <p className="text-xs text-muted-foreground">
                {data?.additional_statistics?.completed_tasks || 0} of {data?.additional_statistics?.total_tasks || 0} tasks
              </p>
            </CardContent>
          </Card>

          {/* Rejection Rate */}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Rejection Rate</CardTitle>
              <XCircle className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {data?.additional_statistics?.rejection_rate || 0}%
              </div>
              <p className="text-xs text-muted-foreground">
                {data?.additional_statistics?.rejected_tasks || 0} rejected tasks
              </p>
            </CardContent>
          </Card>

          {/* Deliverable Rate */}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Deliverable Rate</CardTitle>
              <FileText className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {data?.additional_statistics?.deliverable_rate || 0}%
              </div>
              <p className="text-xs text-muted-foreground">
                {data?.additional_statistics?.tasks_with_deliverables || 0} tasks with deliverables
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Additional Charts */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
          {/* Rejected Tasks Trend */}
          <Card className="col-span-4">
            <CardHeader>
              <CardTitle>Rejected Tasks Trend</CardTitle>
              <CardDescription>Daily rejected tasks over the last week</CardDescription>
            </CardHeader>
            <CardContent>
              {data?.rejected_tasks_trend && data.rejected_tasks_trend.length > 0 ? (
                <Line 
                  data={{
                    labels: data.rejected_tasks_trend.map(item => new Date(item.date).toLocaleDateString()),
                    datasets: [{
                      label: 'Rejected Tasks',
                      data: data.rejected_tasks_trend.map(item => item.rejected_tasks),
                      borderColor: 'rgb(239, 68, 68)',
                      backgroundColor: 'rgba(239, 68, 68, 0.1)',
                      tension: 0.1,
                    }]
                  }}
                  options={{
                    responsive: true,
                    plugins: {
                      legend: {
                        display: false,
                      },
                    },
                    scales: {
                      y: {
                        beginAtZero: true,
                      },
                    },
                  }}
                />
              ) : (
                <div className="flex items-center justify-center h-64 text-muted-foreground">
                  No rejected tasks data available
                </div>
              )}
            </CardContent>
          </Card>

          {/* Tasks with Unchecked Checklists */}
          <Card className="col-span-3">
            <CardHeader>
              <CardTitle>Checklist Compliance</CardTitle>
              <CardDescription>Tasks completed with unchecked items</CardDescription>
            </CardHeader>
            <CardContent>
              {data?.tasks_with_unchecked_checklists ? (
                <div className="space-y-4">
                  <div className="text-center">
                    <div className="text-3xl font-bold text-red-600">
                      {data.tasks_with_unchecked_checklists.percentage_with_unchecked}%
                    </div>
                    <p className="text-sm text-muted-foreground">
                      {data.tasks_with_unchecked_checklists.completed_with_unchecked_items} of {data.tasks_with_unchecked_checklists.total_completed_with_checklists} tasks
                    </p>
                  </div>
                  {data.tasks_with_unchecked_checklists.tasks_list.length > 0 && (
                    <div className="space-y-2">
                      <p className="text-xs font-medium text-muted-foreground">Recent examples:</p>
                      {data.tasks_with_unchecked_checklists.tasks_list.slice(0, 3).map((task, index) => (
                        <div key={index} className="text-xs p-2 bg-red-50 rounded">
                          <div className="font-medium">{task.title}</div>
                          <div className="text-red-600">
                            {task.unchecked_items} of {task.total_items} items unchecked
                          </div>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              ) : (
                <div className="flex items-center justify-center h-64 text-muted-foreground">
                  No checklist data available
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Overdue Tasks */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <div>
                <CardTitle className="flex items-center gap-2">
                  <Clock className="h-5 w-5 text-red-500" />
                  Overdue Tasks
                </CardTitle>
                <CardDescription>Tasks that have passed their deadline and need immediate attention</CardDescription>
              </div>
              <Button variant="outline" size="sm" onClick={() => router.push('/tasks')}>
                View All Tasks
              </Button>
            </div>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {data?.overdue_tasks && data.overdue_tasks.length > 0 ? (
                data.overdue_tasks.slice(0, 5).map((task, index: number) => (
                  <div key={index} className="flex items-center justify-between p-3 border border-red-200 rounded-lg bg-red-50 overflow-x-auto">
                    <div className="flex-1">
                      <div className="flex items-center gap-2 mb-1">
                        <h4 className="font-medium text-sm text-red-800">{task.title}</h4>
                        {task.priority && (
                          <Badge variant="secondary" className="text-xs bg-red-100 text-red-800">
                            {task.priority.title}
                          </Badge>
                        )}
                        {task.status && (
                          <Badge variant="secondary" className="text-xs">
                            {task.status.title}
                          </Badge>
                        )}
                      </div>
                      <div className="flex items-center gap-4 text-xs text-red-600">
                        <span>Due: {new Date(task.end_date).toLocaleDateString()}</span>
                        <span>Overdue: {Math.ceil((new Date().getTime() - new Date(task.end_date).getTime()) / (1000 * 60 * 60 * 24))} days</span>
                        {task.project && (
                          <span>Project: {task.project.title}</span>
                        )}
                        {task.clients && task.clients.length > 0 && task.clients[0] && (
                          <span>Client: {task.clients[0].first_name && task.clients[0].last_name ? 
                            `${task.clients[0].first_name} ${task.clients[0].last_name}` : 
                            task.clients[0].name || 'Unknown'}</span>
                        )}
                        {task.users && task.users.length > 0 && task.users[0] && (
                          <span>Assigned: {task.users[0].first_name} {task.users[0].last_name}</span>
                        )}
                      </div>
                      {task.description && (
                        <p className="text-xs text-red-600 line-clamp-1 mt-1">
                          {task.description}
                        </p>
                      )}
                    </div>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => router.push(`/tasks/${task.id}`)}
                      className="text-red-600 hover:text-red-800"
                    >
                      View
                    </Button>
                  </div>
                ))
              ) : (
                <div className="text-center py-8">
                  <Clock className="h-12 w-12 text-green-400 mx-auto mb-4" />
                  <p className="text-green-600 font-medium">No overdue tasks</p>
                  <p className="text-sm text-muted-foreground">All tasks are on schedule!</p>
                </div>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Project Management Overview */}
        {data?.project_management && data.project_management.length > 0 && (
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <div>
                  <CardTitle>Project Management</CardTitle>
                  <CardDescription>Overview of active projects and their status</CardDescription>
                </div>
                <Button variant="outline" size="sm" onClick={() => router.push('/project-management')}>
                  View All Projects
                </Button>
              </div>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {data.project_management.slice(0, 5).map((project: any, index: number) => (
                  <div key={index} className="flex items-center justify-between p-3 border rounded-lg">
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
                        <span>{project.total_tasks || 0} total tasks</span>
                        <span>{project.active_tasks || 0} active</span>
                        <span>{project.overdue_tasks || 0} overdue</span>
                        {project.clients && project.clients.length > 0 && (
                          <span>{project.clients.map((c: any) => c.name).join(', ')}</span>
                        )}
                      </div>
                    </div>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => router.push(`/projects/${project.id}`)}
                    >
                      View
                    </Button>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        )}

        {/* Recent Tasks */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <div>
                <CardTitle>Recent Tasks</CardTitle>
                <CardDescription>Latest tasks created in the system</CardDescription>
              </div>
              <Button variant="outline" size="sm" onClick={() => router.push('/tasks')}>
                View All Tasks
              </Button>
            </div>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {data?.recent_tasks && data.recent_tasks.length > 0 ? (
                data.recent_tasks.slice(0, 5).map((task, index: number) => (
                  <div key={index} className="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 cursor-pointer" onClick={() => router.push(`/tasks/${task.id}`)}>
                    <div className="flex items-center space-x-4">
                      <div className="w-2 h-2 bg-primary rounded-full" />
                      <div className="flex-1 space-y-1">
                        <p className="text-sm">
                          <span className="font-medium">{task.title || 'Unnamed Task'}</span>
                          {task.priority && (
                            <span className="ml-2 text-xs px-2 py-1 rounded-full bg-secondary">
                              {task.priority.title}
                            </span>
                          )}
                          {task.template && (
                            <span className="ml-2 text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                              {task.template.title}
                            </span>
                          )}
                          {task.deliverable_quantity && task.deliverable_quantity > 1 && (
                            <span className="ml-2 text-xs px-2 py-1 rounded-full bg-green-100 text-green-800">
                              Qty: {task.deliverable_quantity}
                            </span>
                          )}
                          {task.deliverables_count && task.deliverables_count > 0 && (
                            <span className="ml-2 text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-800">
                              📎 {task.deliverables_count}
                            </span>
                          )}
                        </p>
                        <p className="text-xs text-muted-foreground">
                          {task.status ? `Status: ${task.status.title}` : 'No status'} •{' '}
                          {new Date(task.created_at).toLocaleDateString()}
                          {task.clients && task.clients.length > 0 && task.clients[0] && (
                            <span> • Client: {task.clients[0].first_name && task.clients[0].last_name ? 
                              `${task.clients[0].first_name} ${task.clients[0].last_name}` : 
                              task.clients[0].name || 'Unknown'}</span>
                          )}
                          {task.users && task.users.length > 0 && task.users[0] && (
                            <span> • Tasker: {task.users[0].first_name} {task.users[0].last_name}</span>
                          )}
                        </p>
                        {task.description && (
                          <p className="text-xs text-muted-foreground line-clamp-1">
                            {task.description}
                          </p>
                        )}
                      </div>
                    </div>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={(e) => {
                        e.stopPropagation();
                        router.push(`/tasks/${task.id}`);
                      }}
                    >
                      View
                    </Button>
                  </div>
                ))
              ) : (
                <p className="text-muted-foreground text-center py-4">No recent tasks</p>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Active Users */}
        <Card>
          <CardHeader>
            <CardTitle>Active Users</CardTitle>
            <CardDescription>Most active users this week</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {data?.user_activity && data.user_activity.length > 0 ? (
                data.user_activity.map((user: any, index: number) => (
                  <div key={index} className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                      <div className="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                        <span className="text-xs font-medium">
                          {user.first_name?.[0]}{user.last_name?.[0]}
                        </span>
                      </div>
                      <div>
                        <p className="text-sm font-medium">{user.first_name} {user.last_name}</p>
                        {user.email && (
                          <p className="text-xs text-muted-foreground">{user.email}</p>
                        )}
                      </div>
                    </div>
                    <div className="text-sm text-muted-foreground">
                      {user.recent_tasks || 0} tasks
                    </div>
                  </div>
                ))
              ) : (
                <p className="text-muted-foreground text-center py-4">No active users</p>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}