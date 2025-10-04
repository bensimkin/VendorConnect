'use client';

import { useState, useEffect } from 'react';
import { useRouter, useParams, useSearchParams } from 'next/navigation';
import { toast } from 'react-hot-toast';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, Users, Calendar, Building, DollarSign, CheckCircle, Clock, AlertCircle, Trash2 } from 'lucide-react';
import { apiClient } from '@/lib/api-client';
import { format } from 'date-fns';

interface Project {
  id: number;
  title: string;
  description?: string;
  start_date: string;
  end_date?: string;
  status_id: number;
  budget?: number;
  created_at: string;
  updated_at: string;
  status?: { id: number; title: string };
  clients?: Array<{ id: number; name: string; first_name?: string; last_name?: string; company?: string }>;
  users?: Array<{ id: number; first_name: string; last_name: string; email: string }>;
  tasks?: Array<{ 
    id: number; 
    title: string; 
    description?: string;
    status: { title: string };
    priority?: { title: string };
    users?: Array<{ id: number; first_name: string; last_name: string }>;
    start_date?: string;
    end_date?: string;
    created_at: string;
    deliverables_count?: number;
  }>;
}

export default function ProjectDetailPage() {
  const router = useRouter();
  const params = useParams();
  const searchParams = useSearchParams();
  const projectId = params.id as string;

  const [project, setProject] = useState<Project | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (projectId) {
      fetchProject();
    }
  }, [projectId, searchParams]);

  const fetchProject = async () => {
    try {
      const response = await apiClient.get(`/projects/${projectId}`);
      setProject(response.data.data);
    } catch (error: any) {
      console.error('Failed to fetch project:', error);
      toast.error('Failed to load project details');
      router.push('/projects');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async () => {
    if (!project) return;

    if (!confirm(`Are you sure you want to delete "${project.title}"? This action cannot be undone.`)) {
      return;
    }

    try {
      await apiClient.delete(`/projects/${projectId}`);
      toast.success('Project deleted successfully');
      router.push('/projects');
    } catch (error: any) {
      console.error('Failed to delete project:', error);
      toast.error(error.response?.data?.message || 'Failed to delete project');
    }
  };

  const getStatusIcon = (statusName?: string) => {
    if (!statusName) return <Clock className="h-5 w-5 text-gray-500" />;
    
    switch (statusName.toLowerCase()) {
      case 'completed':
        return <CheckCircle className="h-5 w-5 text-green-500" />;
      case 'in progress':
      case 'active':
        return <Clock className="h-5 w-5 text-blue-500" />;
      case 'on hold':
        return <AlertCircle className="h-5 w-5 text-yellow-500" />;
      default:
        return <Clock className="h-5 w-5 text-gray-500" />;
    }
  };

  const getStatusBadge = (statusName?: string) => {
    if (!statusName) return (
      <Badge className="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
        No Status
      </Badge>
    );
    
    switch (statusName.toLowerCase()) {
      case 'completed':
        return <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Completed</Badge>;
      case 'in progress':
      case 'active':
        return <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Active</Badge>;
      case 'on hold':
        return <Badge className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">On Hold</Badge>;
      default:
        return <Badge className="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">{statusName}</Badge>;
    }
  };

  const formatDate = (dateString: string) => {
    try {
      return format(new Date(dateString), 'MMM dd, yyyy');
    } catch {
      return 'Invalid date';
    }
  };

  const calculateProgress = () => {
    if (!project?.tasks || project.tasks.length === 0) return 0;
    const completedTasks = project.tasks.filter(task => 
      task.status?.title?.toLowerCase() === 'completed'
    ).length;
    return Math.round((completedTasks / project.tasks.length) * 100);
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading project details...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  if (!project) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <p className="text-muted-foreground">Project not found</p>
            <Button onClick={() => router.push('/projects')} className="mt-4">
              Back to Projects
            </Button>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center gap-4">
          <Button
            variant="ghost"
            size="icon"
            onClick={() => router.push('/projects')}
          >
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div className="flex-1">
            <h1 className="text-2xl font-bold">{project.title}</h1>
            <p className="text-muted-foreground">Project Details</p>
          </div>
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              onClick={() => router.push(`/projects/${projectId}/edit`)}
            >
              <Edit className="mr-2 h-4 w-4" />
              Edit Project
            </Button>
            <Button
              variant="destructive"
              onClick={handleDelete}
            >
              <Trash2 className="mr-2 h-4 w-4" />
              Delete
            </Button>
          </div>
        </div>

        <div className="grid gap-6 md:grid-cols-2">
          {/* Basic Information */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Building className="h-5 w-5" />
                Basic Information
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Project Name</span>
                <span className="text-sm font-medium">{project.title}</span>
              </div>
              {project.description && (
                <div className="flex items-start justify-between">
                  <span className="text-sm font-medium text-muted-foreground">Description</span>
                  <span className="text-sm text-right max-w-xs">{project.description}</span>
                </div>
              )}
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Status</span>
                <div className="flex items-center gap-2">
                  {getStatusIcon(project.status?.title)}
                  {getStatusBadge(project.status?.title)}
                </div>
              </div>
              {project.budget && (
                <div className="flex items-center justify-between">
                  <span className="text-sm font-medium text-muted-foreground">Budget</span>
                  <span className="text-sm flex items-center gap-1">
                    <DollarSign className="h-4 w-4" />
                    {project.budget.toLocaleString()}
                  </span>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Timeline Information */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Calendar className="h-5 w-5" />
                Timeline
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Start Date</span>
                <span className="text-sm">{formatDate(project.start_date)}</span>
              </div>
              {project.end_date && (
                <div className="flex items-center justify-between">
                  <span className="text-sm font-medium text-muted-foreground">End Date</span>
                  <span className="text-sm">{formatDate(project.end_date)}</span>
                </div>
              )}
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Created</span>
                <span className="text-sm">{formatDate(project.created_at)}</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Last Updated</span>
                <span className="text-sm">{formatDate(project.updated_at)}</span>
              </div>
            </CardContent>
          </Card>

          {/* Clients */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Building className="h-5 w-5" />
                Clients
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {project.clients && project.clients.length > 0 ? (
                <div className="space-y-2">
                  {project.clients.map((client) => (
                    <div key={client.id} className="flex items-center justify-between p-2 bg-secondary rounded-md">
                      <div>
                        <p className="text-sm font-medium">{`${client.first_name || ''} ${client.last_name || ''}`.trim()}</p>
                        {client.company && (
                          <p className="text-xs text-muted-foreground">{client.company}</p>
                        )}
                      </div>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => router.push(`/clients/${client.id}`)}
                      >
                        View
                      </Button>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-sm text-muted-foreground">No clients assigned</p>
              )}
            </CardContent>
          </Card>

          {/* Team Members */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Users className="h-5 w-5" />
                Team Members
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {project.users && project.users.length > 0 ? (
                <div className="space-y-2">
                  {project.users.map((user) => (
                    <div key={user.id} className="flex items-center justify-between p-2 bg-secondary rounded-md">
                      <div>
                        <p className="text-sm font-medium">{user.first_name} {user.last_name}</p>
                        <p className="text-xs text-muted-foreground">{user.email}</p>
                      </div>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => router.push(`/users/${user.id}`)}
                      >
                        View
                      </Button>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-sm text-muted-foreground">No team members assigned</p>
              )}
            </CardContent>
          </Card>

          {/* Tasks List */}
          <Card className="md:col-span-2">
            <CardHeader>
              <div className="flex items-center justify-between">
                <CardTitle className="flex items-center gap-2">
                  <CheckCircle className="h-5 w-5" />
                  Project Tasks
                </CardTitle>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => router.push(`/tasks/new?project_id=${project.id}`)}
                >
                  Add Task
                </Button>
              </div>
            </CardHeader>
            <CardContent className="space-y-4">
              {project.tasks && project.tasks.length > 0 ? (
                <div className="space-y-3">
                  {project.tasks.map((task) => (
                    <div
                      key={task.id}
                      className="flex items-center justify-between p-4 border rounded-lg hover:bg-secondary/50 transition-colors cursor-pointer"
                      onClick={() => router.push(`/tasks/${task.id}`)}
                    >
                      <div className="flex items-center gap-3 flex-1">
                        {getStatusIcon(task.status?.title)}
                        <div className="flex-1">
                          <div className="flex items-center gap-2 mb-1">
                            <h3 className="font-medium text-sm">{task.title}</h3>
                            {task.priority && (
                              <Badge variant="secondary" className="text-xs">
                                {task.priority.title}
                              </Badge>
                            )}
                            {task.deliverables_count && task.deliverables_count > 0 && (
                              <Badge variant="secondary" className="text-xs bg-purple-100 text-purple-800">
                                📎 {task.deliverables_count}
                              </Badge>
                            )}
                          </div>
                          <div className="flex items-center gap-4 text-xs text-muted-foreground">
                            {task.status && (
                              <Badge variant="secondary" className="text-xs">
                                {task.status.title}
                              </Badge>
                            )}
                            {task.end_date && (
                              <span>Due: {formatDate(task.end_date)}</span>
                            )}
                            {task.users && task.users.length > 0 && (
                              <span>Assigned: {task.users.map(u => `${u.first_name} ${u.last_name}`).join(', ')}</span>
                            )}
                          </div>
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
                  ))}
                </div>
              ) : (
                <div className="text-center py-8">
                  <CheckCircle className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <p className="text-muted-foreground mb-4">No tasks created for this project yet</p>
                  <Button
                    variant="outline"
                    onClick={() => router.push(`/tasks/new?project_id=${project.id}`)}
                  >
                    Create First Task
                  </Button>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Progress */}
          <Card className="md:col-span-2">
            <CardHeader>
              <CardTitle>Progress</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex justify-between items-center">
                <span className="text-sm font-medium">Task Completion</span>
                <span className="text-sm font-medium">{calculateProgress()}%</span>
              </div>
              <div className="w-full bg-secondary rounded-full h-2">
                <div
                  className="bg-primary rounded-full h-2 transition-all"
                  style={{ width: `${calculateProgress()}%` }}
                />
              </div>
              <div className="grid grid-cols-2 gap-4 text-sm">
                <div className="text-center p-3 bg-secondary rounded-md">
                  <p className="text-2xl font-bold">{project.tasks?.length || 0}</p>
                  <p className="text-muted-foreground">Total Tasks</p>
                </div>
                <div className="text-center p-3 bg-secondary rounded-md">
                  <p className="text-2xl font-bold">
                    {project.tasks?.filter(task => 
                      task.status?.title?.toLowerCase() === 'completed'
                    ).length || 0}
                  </p>
                  <p className="text-muted-foreground">Completed</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </MainLayout>
  );
}
