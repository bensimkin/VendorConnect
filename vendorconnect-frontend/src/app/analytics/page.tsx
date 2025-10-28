'use client';

import { useState, useEffect } from 'react';
import { toast } from 'react-hot-toast';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { BarChart3, Eye, XCircle, LogIn, Activity, TrendingUp, MessageSquare } from 'lucide-react';
import { apiClient } from '@/lib/api-client';
import { useRouter } from 'next/navigation';
import TaskViewAnalytics from '@/components/analytics/TaskViewAnalytics';
import TaskRejectionAnalytics from '@/components/analytics/TaskRejectionAnalytics';
import SessionAnalytics from '@/components/analytics/SessionAnalytics';
import UserTaskActivityAnalytics from '@/components/analytics/UserTaskActivityAnalytics';
import ProjectMetricsBaselines from '@/components/analytics/ProjectMetricsBaselines';
import CommentActivityAnalytics from '@/components/analytics/CommentActivityAnalytics';

interface TaskViewStatistics {
  period: {
    start_date: string;
    end_date: string;
  };
  overview: {
    total_views: number;
    unique_tasks_viewed: number;
    unique_users: number;
    average_view_duration_seconds: number | null;
  };
  most_viewed_tasks: Array<{
    task_id: number;
    task_title: string;
    view_count: number;
  }>;
  top_users: Array<{
    user_id: number;
    user_name: string;
    user_email: string;
    view_count: number;
  }>;
  unviewed_tasks: Array<{
    task_id: number;
    task_title: string;
    assigned_users: string[];
    status_title: string | null;
    priority_title: string | null;
    days_since_assignment: number;
  }>;
}

interface TaskRejectionStatistics {
  period: {
    start_date: string;
    end_date: string;
  };
  overview: {
    total_rejections: number;
    unique_tasks_rejected: number;
    unique_users_rejecting: number;
  };
  top_rejecting_users: Array<{
    user_id: number;
    user_name: string;
    user_email: string;
    rejection_count: number;
  }>;
  most_rejected_tasks: Array<{
    task_id: number;
    task_title: string;
    task_type: string | null;
    rejection_count: number;
  }>;
  recent_rejections: Array<{
    rejection_id: number;
    task_id: number;
    task_title: string;
    user_id: number;
    user_name: string;
    user_email: string;
    rejected_at: string;
    reason: string | null;
  }>;
}

interface SessionStatistics {
  period: {
    start_date: string;
    end_date: string;
  };
  overview: {
    total_sessions: number;
    active_users: number;
    unique_users: number;
    average_session_duration_seconds: number | null;
  };
  top_users_by_sessions: Array<{
    user_id: number;
    user_name: string;
    user_email: string;
    session_count: number;
    avg_duration_seconds: number | null;
  }>;
  recent_sessions: Array<{
    session_id: number;
    user_id: number;
    user_name: string;
    login_at: string;
    logout_at: string | null;
    duration_seconds: number | null;
  }>;
  inactive_users: Array<{
    user_id: number;
    user_name: string;
    user_email: string;
    last_login_at: string;
    active_tasks_count: number;
    days_since_login: number;
  }>;
  session_activity_trend: Array<{
    date: string;
    active_users: number;
    total_sessions: number;
  }>;
}

interface UserTaskActivityStatistics {
  period: {
    start_date: string;
    end_date: string;
  };
  overview: {
    total_activities: number;
    unique_tasks_with_activity: number;
    unique_users_with_activity: number;
  };
  tasks_with_no_activity: Array<{
    task_id: number;
    task_title: string;
    status_title: string | null;
    priority_title: string | null;
    assigned_users: string[];
    days_since_activity: number;
  }>;
  users_by_activity_level: Array<{
    user_id: number;
    user_name: string;
    user_email: string;
    active_tasks_count: number;
    most_recent_activity: string;
  }>;
  most_active_tasks: Array<{
    task_id: number;
    task_title: string;
    active_users_count: number;
    last_activity: string;
  }>;
  activity_trend: Array<{
    date: string;
    active_tasks: number;
    active_users: number;
  }>;
}

interface ProjectMetricsBaselinesData {
  baselines: {
    overall_metrics: Array<{
      metric_name: string;
      metric_value: number;
      sample_size: number;
      calculated_at: string;
    }>;
    by_client: Array<{
      metric_name: string;
      metric_value: number;
      sample_size: number;
      client: {
        id: number;
        name: string;
      };
      calculated_at: string;
    }>;
    by_task_type: Array<{
      metric_name: string;
      metric_value: number;
      sample_size: number;
      task_type: {
        id: number;
        name: string;
      };
      calculated_at: string;
    }>;
  };
  summary: {
    total_baselines: number;
    last_calculated: string | null;
  };
}

export default function AnalyticsPage() {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState<'views' | 'rejections' | 'sessions' | 'activity' | 'baselines' | 'comments'>('views');
  const [loadingViews, setLoadingViews] = useState(true);
  const [loadingRejections, setLoadingRejections] = useState(true);
  const [loadingSessions, setLoadingSessions] = useState(true);
  const [loadingActivity, setLoadingActivity] = useState(true);
  const [loadingBaselines, setLoadingBaselines] = useState(true);
  const [loadingComments, setLoadingComments] = useState(true);
  const [viewStatistics, setViewStatistics] = useState<TaskViewStatistics | null>(null);
  const [rejectionStatistics, setRejectionStatistics] = useState<TaskRejectionStatistics | null>(null);
  const [sessionStatistics, setSessionStatistics] = useState<SessionStatistics | null>(null);
  const [activityStatistics, setActivityStatistics] = useState<UserTaskActivityStatistics | null>(null);
  const [baselinesData, setBaselinesData] = useState<ProjectMetricsBaselinesData | null>(null);
  const [commentStatistics, setCommentStatistics] = useState<any>(null);
  
  // Default to 7 days ago
  const getDefaultStartDate = () => {
    const date = new Date();
    date.setDate(date.getDate() - 7);
    return date.toISOString().split('T')[0];
  };
  
  const getDefaultEndDate = () => {
    const date = new Date();
    date.setDate(date.getDate() + 1); // add 1 day
    return date.toISOString().split('T')[0];
  };

  const [startDate, setStartDate] = useState(getDefaultStartDate());
  const [endDate, setEndDate] = useState(getDefaultEndDate());

  useEffect(() => {
    fetchViewStatistics();
    fetchRejectionStatistics();
    fetchSessionStatistics();
    fetchActivityStatistics();
    fetchBaselinesData();
    fetchCommentStatistics();
  }, []);

  const fetchViewStatistics = async () => {
    try {
      setLoadingViews(true);
      const params = new URLSearchParams();
      if (startDate) params.append('start_date', startDate);
      if (endDate) params.append('end_date', endDate);

      const response = await apiClient.get(`/analytics/task-views/statistics?${params.toString()}`);
      setViewStatistics(response.data.data);
    } catch (error: any) {
      if (error.response?.status === 403) {
        toast.error('Access denied. Only admins can view analytics.');
        router.push('/dashboard');
      } else {
        toast.error('Failed to load view analytics: ' + (error.response?.data?.message || error.message));
      }
    } finally {
      setLoadingViews(false);
    }
  };

  const fetchRejectionStatistics = async () => {
    try {
      setLoadingRejections(true);
      const params = new URLSearchParams();
      if (startDate) params.append('start_date', startDate);
      if (endDate) params.append('end_date', endDate);

      const response = await apiClient.get(`/analytics/task-rejections/statistics?${params.toString()}`);
      setRejectionStatistics(response.data.data);
    } catch (error: any) {
      if (error.response?.status === 403) {
        // Already handled in views
      } else {
        toast.error('Failed to load rejection analytics: ' + (error.response?.data?.message || error.message));
      }
    } finally {
      setLoadingRejections(false);
    }
  };

  const fetchSessionStatistics = async () => {
    try {
      setLoadingSessions(true);
      const params = new URLSearchParams();
      if (startDate) params.append('start_date', startDate);
      if (endDate) params.append('end_date', endDate);

      const response = await apiClient.get(`/analytics/user-sessions/statistics?${params.toString()}`);
      setSessionStatistics(response.data.data);
    } catch (error: any) {
      if (error.response?.status === 403) {
        // Already handled in views
      } else {
        toast.error('Failed to load session analytics: ' + (error.response?.data?.message || error.message));
      }
    } finally {
      setLoadingSessions(false);
    }
  };

  const fetchActivityStatistics = async () => {
    try {
      setLoadingActivity(true);
      const params = new URLSearchParams();
      if (startDate) params.append('start_date', startDate);
      if (endDate) params.append('end_date', endDate);

      const response = await apiClient.get(`/analytics/user-task-activity/statistics?${params.toString()}`);
      setActivityStatistics(response.data.data);
    } catch (error: any) {
      if (error.response?.status === 403) {
        // Already handled in views
      } else {
        toast.error('Failed to load activity analytics: ' + (error.response?.data?.message || error.message));
      }
    } finally {
      setLoadingActivity(false);
    }
  };

  const fetchBaselinesData = async () => {
    try {
      setLoadingBaselines(true);
      const response = await apiClient.get('/analytics/project-metrics-baselines');
      setBaselinesData(response.data.data);
    } catch (error: any) {
      if (error.response?.status === 403) {
        // Already handled in views
      } else {
        toast.error('Failed to load baseline metrics: ' + (error.response?.data?.message || error.message));
      }
    } finally {
      setLoadingBaselines(false);
    }
  };

  const fetchCommentStatistics = async () => {
    try {
      setLoadingComments(true);
      const params = new URLSearchParams();
      if (startDate) params.append('start_date', startDate);
      if (endDate) params.append('end_date', endDate);

      const response = await apiClient.get(`/analytics/comment-activity/statistics?${params.toString()}`);
      setCommentStatistics(response.data.data);
    } catch (error: any) {
      if (error.response?.status === 403) {
        // Already handled in views
      } else {
        toast.error('Failed to load comment activity: ' + (error.response?.data?.message || error.message));
      }
    } finally {
      setLoadingComments(false);
    }
  };

  const handleDateRangeChange = () => {
    fetchViewStatistics();
    fetchRejectionStatistics();
    fetchSessionStatistics();
    fetchActivityStatistics();
  };

  const formatDuration = (seconds: number | null) => {
    if (!seconds) return 'N/A';
    if (seconds < 60) return `${seconds}s`;
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m ${seconds % 60}s`;
    return `${Math.floor(seconds / 3600)}h ${Math.floor((seconds % 3600) / 60)}m`;
  };

  const loading = loadingViews || loadingRejections || loadingSessions || loadingActivity || loadingBaselines || loadingComments;

  return (
    <MainLayout>
      <div className="container mx-auto p-6">
        <div className="mb-6">
          <div className="flex items-center justify-between mb-4">
            <h1 className="text-3xl font-bold flex items-center gap-2">
              <BarChart3 className="w-8 h-8" />
              Analytics Dashboard
            </h1>
          </div>

          {/* Date Range Filter */}
          <Card className="mb-6">
            <CardHeader>
              <CardTitle>Date Range</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex gap-4 items-end flex-wrap">
                <div className="flex-1">
                  <Label htmlFor="start-date">Start Date</Label>
                  <Input
                    id="start-date"
                    type="date"
                    value={startDate}
                    onChange={(e) => setStartDate(e.target.value)}
                  />
                </div>
                <div className="flex-1">
                  <Label htmlFor="end-date">End Date</Label>
                  <Input
                    id="end-date"
                    type="date"
                    value={endDate}
                    onChange={(e) => setEndDate(e.target.value)}
                  />
                </div>
                <Button onClick={handleDateRangeChange}>Apply Filter</Button>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Tabs */}
        <div className="mb-6">
          <div className="border-b border-gray-200">
            <nav className="-mb-px flex space-x-8 overflow-x-auto">
              <button
                onClick={() => setActiveTab('views')}
                className={`
                  inline-flex items-center gap-2 py-4 px-1 border-b-2 font-medium text-sm
                  ${activeTab === 'views'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  }
                `}
              >
                <Eye className="w-5 h-5" />
                Task Views
              </button>
              <button
                onClick={() => setActiveTab('rejections')}
                className={`
                  inline-flex items-center gap-2 py-4 px-1 border-b-2 font-medium text-sm
                  ${activeTab === 'rejections'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  }
                `}
              >
                <XCircle className="w-5 h-5" />
                Task Rejections
              </button>
              <button
                onClick={() => setActiveTab('sessions')}
                className={`
                  inline-flex items-center gap-2 py-4 px-1 border-b-2 font-medium text-sm
                  ${activeTab === 'sessions'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  }
                `}
              >
                <LogIn className="w-5 h-5" />
                Platform Usage
              </button>
              <button
                onClick={() => setActiveTab('activity')}
                className={`
                  inline-flex items-center gap-2 py-4 px-1 border-b-2 font-medium text-sm
                  ${activeTab === 'activity'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  }
                `}
              >
                <Activity className="w-5 h-5" />
                Task Activity
              </button>
              <button
                onClick={() => setActiveTab('baselines')}
                className={`
                  inline-flex items-center gap-2 py-4 px-1 border-b-2 font-medium text-sm
                  ${activeTab === 'baselines'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  }
                `}
              >
                <TrendingUp className="w-5 h-5" />
                Project Baselines
              </button>
              <button
                onClick={() => setActiveTab('comments')}
                className={`
                  inline-flex items-center gap-2 py-4 px-1 border-b-2 font-medium text-sm
                  ${activeTab === 'comments'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  }
                `}
              >
                <MessageSquare className="w-5 h-5" />
                Comment Activity
              </button>
            </nav>
          </div>
        </div>

        {/* Tab Content */}
        {loading ? (
          <div className="flex items-center justify-center h-64">
            <div className="text-center">
              <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mx-auto mb-4"></div>
              <p className="text-gray-600">Loading analytics...</p>
            </div>
          </div>
        ) : (
          <>
            {activeTab === 'views' && viewStatistics && (
              <TaskViewAnalytics statistics={viewStatistics} formatDuration={formatDuration} />
            )}
            {activeTab === 'rejections' && rejectionStatistics && (
              <TaskRejectionAnalytics statistics={rejectionStatistics} />
            )}
            {activeTab === 'sessions' && sessionStatistics && (
              <SessionAnalytics statistics={sessionStatistics} formatDuration={formatDuration} />
            )}
            {activeTab === 'activity' && activityStatistics && (
              <UserTaskActivityAnalytics statistics={activityStatistics} />
            )}
            {activeTab === 'baselines' && baselinesData && (
              <ProjectMetricsBaselines 
                baselines={baselinesData.baselines}
                summary={baselinesData.summary}
              />
            )}
            {activeTab === 'comments' && commentStatistics && (
              <CommentActivityAnalytics statistics={commentStatistics} />
            )}
          </>
        )}
      </div>
    </MainLayout>
  );
}
