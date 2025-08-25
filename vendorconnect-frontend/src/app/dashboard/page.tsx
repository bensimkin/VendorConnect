'use client';

import { useEffect, useState } from 'react';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import apiClient from '@/lib/api-client';
import { Activity, Users, CheckCircle2, Clock, TrendingUp, TrendingDown } from 'lucide-react';
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
  recent_tasks: any[];
  user_activity: any[];
  task_trend: Array<{
    date: string;
    completed_tasks: number;
  }>;
  statuses?: Array<{
    id: number;
    title: string;
  }>;
}

export default function DashboardPage() {
  const [data, setData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    try {
      const response = await apiClient.get('/dashboard');
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
        user_activity: [],
        task_trend: [],
      });
    } finally {
      setLoading(false);
    }
  };

  // Calculate task completion stats from by_status
  const getTaskStats = () => {
    if (!data) return { completed: 0, pending: 0, inProgress: 0 };
    
    const byStatus = data.task_statistics.by_status;
    const statuses = data.statuses || [];
    
    // Find status IDs by title
    const completedStatus = statuses.find(s => s.title === 'Completed');
    const pendingStatus = statuses.find(s => s.title === 'Pending');
    const inProgressStatus = statuses.find(s => s.title === 'In Progress');
    
    return {
      completed: completedStatus ? (byStatus[completedStatus.id.toString()] || 0) : 0,
      pending: pendingStatus ? (byStatus[pendingStatus.id.toString()] || 0) : 0,
      inProgress: inProgressStatus ? (byStatus[inProgressStatus.id.toString()] || 0) : 0,
    };
  };

  const taskStats = getTaskStats();

  const taskCompletionData = {
    labels: ['Completed', 'Pending', 'In Progress'],
    datasets: [
      {
        data: [taskStats.completed, taskStats.pending, taskStats.inProgress],
        backgroundColor: ['#10b981', '#f59e0b', '#3b82f6'],
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
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Dashboard</h1>
          <p className="text-muted-foreground">Welcome back! Here's what's happening with your projects.</p>
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

        {/* Recent Tasks */}
        <Card>
          <CardHeader>
            <CardTitle>Recent Tasks</CardTitle>
            <CardDescription>Latest tasks created in the system</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {data?.recent_tasks && data.recent_tasks.length > 0 ? (
                data.recent_tasks.slice(0, 5).map((task: any, index: number) => (
                  <div key={index} className="flex items-center space-x-4">
                    <div className="w-2 h-2 bg-primary rounded-full" />
                    <div className="flex-1 space-y-1">
                      <p className="text-sm">
                        <span className="font-medium">{task.name || 'Unnamed Task'}</span>
                        {task.priority && (
                          <span className="ml-2 text-xs px-2 py-1 rounded-full bg-secondary">
                            {task.priority.name}
                          </span>
                        )}
                      </p>
                      <p className="text-xs text-muted-foreground">
                        {task.status ? `Status: ${task.status.name}` : 'No status'} •{' '}
                        {new Date(task.created_at).toLocaleDateString()}
                      </p>
                    </div>
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
                        <p className="text-xs text-muted-foreground">{user.email}</p>
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