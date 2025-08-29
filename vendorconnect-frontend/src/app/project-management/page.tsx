'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import apiClient from '@/lib/api-client';
import { Plus, Search, Filter, Calendar, User, CheckCircle, Clock, AlertCircle, ChevronDown, ChevronRight, ExternalLink } from 'lucide-react';
import { format } from 'date-fns';

interface Project {
  id: number;
  title: string;
  description?: string;
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
  created_at: string;
  updated_at: string;
  tasks?: Task[];
}

interface Task {
  id: number;
  title: string;
  description?: string;
  status?: {
    id: number;
    title: string;  // FIXED: Use primary database field
  };
  priority?: {
    id: number;
    title: string;  // FIXED: Use primary database field
  };
  users?: Array<{
    id: number;
    first_name: string;
    last_name: string;
  }>;
  start_date?: string;
  end_date?: string;
  created_at: string;
}

export default function ProjectManagementPage() {
  const router = useRouter();
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [expandedProjects, setExpandedProjects] = useState<Set<number>>(new Set());
  const [projectTasks, setProjectTasks] = useState<Record<number, Task[]>>({});

  useEffect(() => {
    fetchProjects();
  }, []);

  // Debounced search effect
  useEffect(() => {
    const timeoutId = setTimeout(() => {
      fetchProjects(searchTerm);
    }, 500);

    return () => clearTimeout(timeoutId);
  }, [searchTerm]);

  const fetchProjects = async (searchQuery = '') => {
    try {
      const params = new URLSearchParams();
      if (searchQuery) {
        params.append('search', searchQuery);
      }
      
      const response = await apiClient.get(`/projects?${params.toString()}`);
      const projectData = response.data.data?.data || response.data.data || [];
      setProjects(Array.isArray(projectData) ? projectData : []);
    } catch (error) {
      console.error('Failed to fetch projects:', error);
      setProjects([]);
    } finally {
      setLoading(false);
    }
  };

  const fetchProjectTasks = async (projectId: number) => {
    try {
      const response = await apiClient.get(`/projects/${projectId}/tasks`);
      const tasks = response.data.data || [];
      setProjectTasks(prev => ({
        ...prev,
        [projectId]: tasks
      }));
    } catch (error) {
      console.error('Failed to fetch project tasks:', error);
    }
  };

  const toggleProjectExpansion = (projectId: number) => {
    const newExpanded = new Set(expandedProjects);
    if (newExpanded.has(projectId)) {
      newExpanded.delete(projectId);
    } else {
      newExpanded.add(projectId);
    }
    setExpandedProjects(newExpanded);
  };

  const getStatusIcon = (statusName?: string) => {
    if (!statusName) return <Clock className="h-4 w-4 text-gray-500" />;
    
    switch (statusName.toLowerCase()) {
      case 'completed':
        return <CheckCircle className="h-4 w-4 text-green-500" />;
      case 'in progress':
      case 'active':
        return <Clock className="h-4 w-4 text-blue-500" />;
      case 'pending':
      case 'accepted':
        return <AlertCircle className="h-4 w-4 text-yellow-500" />;
      case 'overdue':
        return <AlertCircle className="h-4 w-4 text-red-500" />;
      default:
        return <Clock className="h-4 w-4 text-gray-500" />;
    }
  };



  const getStatusColorByName = (statusName?: string) => {
    if (!statusName) return '#6b7280';
    
    switch (statusName.toLowerCase()) {
      case 'completed':
        return '#10b981'; // green
      case 'in progress':
      case 'active':
        return '#3b82f6'; // blue
      case 'pending':
      case 'accepted':
        return '#f59e0b'; // yellow
      case 'overdue':
        return '#ef4444'; // red
      default:
        return '#6b7280'; // gray
    }
  };

  const formatDate = (dateString?: string) => {
    if (!dateString) return 'Not set';
    try {
      return format(new Date(dateString), 'MMM dd, yyyy');
    } catch {
      return 'Invalid date';
    }
  };

  const calculateProgress = (completedTasks: number, totalTasks: number) => {
    if (totalTasks === 0) return 0;
    return Math.round((completedTasks / totalTasks) * 100);
  };

  const calculateOverdueTasks = (project: any) => {
    if (!projectTasks[project.id]) return 0;
    const now = new Date();
    return projectTasks[project.id].filter((task: any) => {
      if (!task.end_date) return false;
      const endDate = new Date(task.end_date);
      const isCompleted = task.status?.title?.toLowerCase() === 'completed';
      return endDate < now && !isCompleted;
    }).length;
  };

  const calculateTaskCounts = (project: Project) => {
    const tasks = project.tasks || [];
    const totalTasks = tasks.length;
    const completedTasks = tasks.filter(task => 
      task.status?.name?.toLowerCase() === 'completed'
    ).length;
    const activeTasks = totalTasks - completedTasks;
    
    const now = new Date();
    const overdueTasks = tasks.filter(task => {
      if (!task.end_date) return false;
      const endDate = new Date(task.end_date);
      const isCompleted = task.status?.title?.toLowerCase() === 'completed';
      return endDate < now && !isCompleted;
    }).length;
    
    return { totalTasks, activeTasks, overdueTasks };
  };

  // Filter projects based on status
  const filteredProjects = projects.filter(project => {
    const matchesStatus = 
      statusFilter === 'all' || 
      (project.status?.title?.toLowerCase() || '') === statusFilter.toLowerCase();
    
    return matchesStatus;
  });

  if (loading) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading projects...</p>
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
            <h1 className="text-3xl font-bold">Project Management</h1>
            <p className="text-muted-foreground">
              Monitor and manage all projects and their tasks
            </p>
          </div>
          <Button onClick={() => router.push('/projects/new')}>
            <Plus className="h-4 w-4 mr-2" />
            New Project
          </Button>
        </div>

        {/* Filters */}
        <Card>
          <CardContent className="p-4">
            <div className="flex gap-4 items-center">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                <Input
                  placeholder="Search projects..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-10"
                />
              </div>
              <Select value={statusFilter} onValueChange={setStatusFilter}>
                <SelectTrigger className="w-48">
                  <SelectValue placeholder="Filter by status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Statuses</SelectItem>
                  <SelectItem value="active">Active</SelectItem>
                  <SelectItem value="inactive">Inactive</SelectItem>
                  <SelectItem value="completed">Completed</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </CardContent>
        </Card>

        {/* Projects List */}
        {filteredProjects.length > 0 ? (
          <div className="space-y-4">
            {filteredProjects.map((project) => (
              <Card key={project.id} className="overflow-hidden">
                <CardHeader 
                  className="cursor-pointer hover:bg-gray-50 transition-colors"
                  onClick={() => toggleProjectExpansion(project.id)}
                >
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                      {expandedProjects.has(project.id) ? (
                        <ChevronDown className="h-5 w-5 text-muted-foreground" />
                      ) : (
                        <ChevronRight className="h-5 w-5 text-muted-foreground" />
                      )}
                      <div className="flex-1">
                        <div className="flex items-center gap-3 mb-2">
                          <CardTitle className="text-lg">{project.title}</CardTitle>
                                                     {project.status && (
                             <Badge variant="secondary">
                               {project.status.title}
                             </Badge>
                           )}
                        </div>
                        <div className="flex items-center gap-6 text-sm text-muted-foreground">
                          <div className="flex items-center gap-1">
                            <Calendar className="h-4 w-4" />
                            <span>Updated {formatDate(project.updated_at)}</span>
                          </div>
                          {project.clients && project.clients.length > 0 && (
                            <div className="flex items-center gap-1">
                              <User className="h-4 w-4" />
                              <span>{project.clients.map(c => c.name).join(', ')}</span>
                            </div>
                          )}
                        </div>
                      </div>
                    </div>
                    <div className="flex items-center gap-4">
                      <div className="text-right">
                        {(() => {
                          const { totalTasks, activeTasks, overdueTasks } = calculateTaskCounts(project);
                          return (
                            <>
                              <div className="text-sm font-medium">
                                {totalTasks} Total Tasks
                              </div>
                              <div className="text-xs text-muted-foreground">
                                {activeTasks} Active • {overdueTasks} Overdue
                              </div>
                            </>
                          );
                        })()}
                      </div>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={(e) => {
                          e.stopPropagation();
                          router.push(`/projects/${project.id}`);
                        }}
                      >
                        <ExternalLink className="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </CardHeader>

                {/* Expanded Tasks Section */}
                {expandedProjects.has(project.id) && (
                  <CardContent className="pt-0">
                    <div className="border-t pt-4">
                      <div className="flex items-center justify-between mb-4">
                        <h3 className="font-medium">Project Tasks</h3>
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => router.push(`/tasks/new?project_id=${project.id}`)}
                        >
                          <Plus className="h-4 w-4 mr-2" />
                          Add Task
                        </Button>
                      </div>
                      
                      {project.tasks && project.tasks.length > 0 ? (
                        <div className="space-y-3">
                          {project.tasks.map((task) => (
                            <div
                              key={task.id}
                              className="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 cursor-pointer"
                              onClick={() => router.push(`/tasks/${task.id}`)}
                            >
                              <div className="flex items-center gap-3">
                                {getStatusIcon(task.status?.title)}
                                <div>
                                  <p className="font-medium text-sm">{task.title}</p>
                                  <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                    {task.status && (
                                      <Badge
                                        variant="secondary"
                                        style={{
                                                          backgroundColor: getStatusColorByName(task.status.title) + '20',
                color: getStatusColorByName(task.status.title),
                                        }}
                                      >
                                        {task.status.title}
                                      </Badge>
                                    )}
                                    {task.priority && (
                                      <Badge
                                        variant="secondary"
                                        className="bg-blue-100 text-blue-800"
                                      >
                                        {task.priority.title}
                                      </Badge>
                                    )}
                                    {task.end_date && (
                                      <span>Due {formatDate(task.end_date)}</span>
                                    )}
                                  </div>
                                </div>
                              </div>
                              <div className="flex items-center gap-2">
                                {task.users && task.users.length > 0 && (
                                  <div className="flex items-center gap-1 text-xs text-muted-foreground">
                                    <User className="h-3 w-3" />
                                    <span>{task.users.length} assigned</span>
                                  </div>
                                )}
                              </div>
                            </div>
                          ))}
                        </div>
                      ) : (
                        <p className="text-center text-muted-foreground py-4">
                          No tasks found for this project
                        </p>
                      )}
                    </div>
                  </CardContent>
                )}
              </Card>
            ))}
          </div>
        ) : (
          <Card>
            <CardContent className="text-center py-12">
              <p className="text-muted-foreground">No projects found</p>
              <Button onClick={() => router.push('/projects/new')} className="mt-4">
                <Plus className="h-4 w-4 mr-2" />
                Create Your First Project
              </Button>
            </CardContent>
          </Card>
        )}
      </div>
    </MainLayout>
  );
}
