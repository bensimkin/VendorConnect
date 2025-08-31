'use client';

import { useState, useEffect, Suspense } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import apiClient from '@/lib/api-client';
import { Plus, Search, Filter, Calendar, User, CheckCircle, Clock, AlertCircle, ChevronDown, ChevronRight, ExternalLink, X } from 'lucide-react';
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
    first_name?: string;
    last_name?: string;
    email?: string;
    company?: string;
    phone?: string;
  }>;
  created_by?: {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
  };
  total_tasks?: number;
  active_tasks?: number;
  overdue_tasks?: number;
  completed_this_week_tasks?: number;
  created_at: string;
  updated_at: string;
  tasks?: Task[];
  budget?: number;
  start_date?: string;
  end_date?: string;
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
    email?: string;
    phone?: string;
  }>;
  created_by?: {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
  };
  start_date?: string;
  end_date?: string;
  created_at: string;
  deliverables_count?: number;
  note?: string;
  standard_brief?: string;
}

interface FilterOption {
  id: number;
  title?: string;
  name?: string;
}

function ProjectManagementPageContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading, setLoading] = useState(true);
  
  // Initialize filter states from URL params or localStorage
  const getInitialFilterValue = (key: string, defaultValue: string = 'all') => {
    const urlValue = searchParams.get(key);
    if (urlValue !== null) return urlValue;
    
    // Fallback to localStorage
    if (typeof window !== 'undefined') {
      const stored = localStorage.getItem(`project_management_filter_${key}`);
      return stored || defaultValue;
    }
    return defaultValue;
  };

  const [searchTerm, setSearchTerm] = useState(getInitialFilterValue('search', ''));
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [expandedProjects, setExpandedProjects] = useState<Set<number>>(new Set());
  const [projectTasks, setProjectTasks] = useState<Record<number, Task[]>>({});
  
  // Enhanced filter states
  const [selectedStatus, setSelectedStatus] = useState(getInitialFilterValue('status'));
  const [selectedClient, setSelectedClient] = useState(getInitialFilterValue('client'));
  
  // Filter options
  const [statuses, setStatuses] = useState<FilterOption[]>([]);
  const [clients, setClients] = useState<FilterOption[]>([]);
  
  // Filter panel state
  const [showFilters, setShowFilters] = useState(false);

  useEffect(() => {
    fetchProjects();
    fetchFilterOptions();
  }, []);

  // Debounced search effect
  useEffect(() => {
    const timeoutId = setTimeout(() => {
      fetchProjects(searchTerm);
    }, 500);

    return () => clearTimeout(timeoutId);
  }, [searchTerm]);

  // Refetch projects when filters change
  useEffect(() => {
    fetchProjects(searchTerm);
  }, [selectedStatus, selectedClient]);

  const fetchFilterOptions = async () => {
    try {
      const [statusesRes, clientsRes] = await Promise.all([
        apiClient.get('/statuses?per_page=all'),
        apiClient.get('/clients?per_page=all')
      ]);

      setStatuses(statusesRes.data?.data || []);
      setClients(clientsRes.data?.data || []);
    } catch (error) {
      console.error('Failed to fetch filter options:', error);
    }
  };

  const fetchProjects = async (searchQuery = '') => {
    try {
      const params = new URLSearchParams();
      if (searchQuery) {
        params.append('search', searchQuery);
      }
      if (selectedStatus !== 'all') {
        params.append('status_id', selectedStatus);
      }
      if (selectedClient !== 'all') {
        params.append('client_id', selectedClient);
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
      task.status?.title?.toLowerCase() === 'completed'
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

  // Use projects directly since filtering is now server-side
  const filteredProjects = projects;

  // Function to update URL and localStorage
  const updateFilterPersistence = (key: string, value: string) => {
    // Update localStorage
    if (typeof window !== 'undefined') {
      localStorage.setItem(`project_management_filter_${key}`, value);
    }
    
    // Update URL params
    const params = new URLSearchParams(searchParams.toString());
    if (value === 'all' || value === '') {
      params.delete(key);
    } else {
      params.set(key, value);
    }
    
    const newUrl = `${window.location.pathname}?${params.toString()}`;
    router.replace(newUrl, { scroll: false });
  };

  const clearAllFilters = () => {
    setSelectedStatus('all');
    setSelectedClient('all');
    setSearchTerm('');
    
    // Clear all persistent storage
    if (typeof window !== 'undefined') {
      localStorage.removeItem('project_management_filter_status');
      localStorage.removeItem('project_management_filter_client');
      localStorage.removeItem('project_management_filter_search');
    }
    
    // Clear URL params
    router.replace(window.location.pathname, { scroll: false });
  };

  const hasActiveFilters = selectedStatus !== 'all' || 
    selectedClient !== 'all' || 
    searchTerm !== '';

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
            <div className="space-y-4">
              {/* Search and Filter Toggle */}
              <div className="flex gap-4 items-center">
                <div className="relative flex-1">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                  <Input
                    placeholder="Search projects..."
                    value={searchTerm}
                    onChange={(e) => {
                      setSearchTerm(e.target.value);
                      updateFilterPersistence('search', e.target.value);
                    }}
                    className="pl-10"
                  />
                </div>
                <Button 
                  variant="outline" 
                  onClick={() => setShowFilters(!showFilters)}
                >
                  <Filter className="mr-2 h-4 w-4" />
                  Filters
                </Button>
                {hasActiveFilters && (
                  <Button 
                    variant="outline" 
                    onClick={clearAllFilters}
                    className="text-red-600 hover:text-red-700"
                  >
                    <X className="mr-2 h-4 w-4" />
                    Clear All
                  </Button>
                )}
              </div>

              {/* Filter Options */}
              {showFilters && (
                <div className="grid gap-4 md:grid-cols-2">
                  {/* Status Filter */}
                  <div className="space-y-2">
                    <label className="text-sm font-medium">Status</label>
                    <Select value={selectedStatus} onValueChange={(value) => {
                      setSelectedStatus(value);
                      updateFilterPersistence('status', value);
                    }}>
                      <SelectTrigger>
                        <SelectValue placeholder="All Statuses" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">All Statuses</SelectItem>
                        {statuses.map((status) => (
                          <SelectItem key={status.id} value={status.id.toString()}>
                            {status.title || status.name}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>

                  {/* Client Filter */}
                  <div className="space-y-2">
                    <label className="text-sm font-medium">Client</label>
                    <Select value={selectedClient} onValueChange={(value) => {
                      setSelectedClient(value);
                      updateFilterPersistence('client', value);
                    }}>
                      <SelectTrigger>
                        <SelectValue placeholder="All Clients" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">All Clients</SelectItem>
                        {clients.map((client) => (
                          <SelectItem key={client.id} value={client.id.toString()}>
                            {client.title || client.name}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                </div>
              )}

              {/* Active Filters Display */}
              {hasActiveFilters && (
                <div className="flex flex-wrap gap-2">
                  {selectedStatus !== 'all' && (
                    <Badge variant="secondary" className="flex items-center gap-1">
                      Status: {statuses.find(s => s.id.toString() === selectedStatus)?.title || selectedStatus}
                      <X 
                        className="h-3 w-3 cursor-pointer" 
                        onClick={() => {
                          setSelectedStatus('all');
                          updateFilterPersistence('status', 'all');
                        }}
                      />
                    </Badge>
                  )}
                  {selectedClient !== 'all' && (
                    <Badge variant="secondary" className="flex items-center gap-1">
                      Client: {clients.find(c => c.id.toString() === selectedClient)?.title || selectedClient}
                      <X 
                        className="h-3 w-3 cursor-pointer" 
                        onClick={() => {
                          setSelectedClient('all');
                          updateFilterPersistence('client', 'all');
                        }}
                      />
                    </Badge>
                  )}
                  {searchTerm && (
                    <Badge variant="secondary" className="flex items-center gap-1">
                      Search: "{searchTerm}"
                      <X 
                        className="h-3 w-3 cursor-pointer" 
                        onClick={() => {
                          setSearchTerm('');
                          updateFilterPersistence('search', '');
                        }}
                      />
                    </Badge>
                  )}
                </div>
              )}
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
                              <span>{project.clients.map(c => c.name || `${c.first_name || ''} ${c.last_name || ''}`.trim()).join(', ')}</span>
                            </div>
                          )}
                          {project.created_by && (
                            <div className="flex items-center gap-1">
                              <User className="h-4 w-4" />
                              <span>Requester: {project.created_by.first_name} {project.created_by.last_name}</span>
                            </div>
                          )}
                          {project.budget && (
                            <div className="flex items-center gap-1">
                              <span>Budget: ${project.budget.toLocaleString()}</span>
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
                                    <span>{task.users.map(u => `${u.first_name} ${u.last_name}`).join(', ')}</span>
                                  </div>
                                )}
                                {task.created_by && (
                                  <div className="flex items-center gap-1 text-xs text-muted-foreground">
                                    <span>Created by: {task.created_by.first_name} {task.created_by.last_name}</span>
                                  </div>
                                )}
                                {task.deliverables_count && task.deliverables_count > 0 && (
                                  <div className="flex items-center gap-1 text-xs text-purple-600">
                                    <span>📎 {task.deliverables_count}</span>
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
                    
                    {/* Enhanced Project Details Section */}
                    <div className="mt-6 border-t pt-4">
                      <h3 className="font-medium mb-4">Project Details</h3>
                      
                      {/* Client Information */}
                      {project.clients && project.clients.length > 0 && (
                        <div className="mb-4">
                          <h4 className="text-sm font-medium text-muted-foreground mb-2">Clients</h4>
                          <div className="space-y-2">
                            {project.clients.map((client) => (
                              <div key={client.id} className="p-3 border rounded-lg bg-gray-50">
                                <div className="flex items-center justify-between">
                                  <div>
                                    <p className="font-medium text-sm">
                                      {client.name || `${client.first_name || ''} ${client.last_name || ''}`.trim()}
                                    </p>
                                    {client.company && (
                                      <p className="text-xs text-muted-foreground">{client.company}</p>
                                    )}
                                    {client.email && (
                                      <p className="text-xs text-muted-foreground">{client.email}</p>
                                    )}
                                    {client.phone && (
                                      <p className="text-xs text-muted-foreground">{client.phone}</p>
                                    )}
                                  </div>
                                  <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => router.push(`/clients/${client.id}`)}
                                  >
                                    <ExternalLink className="h-4 w-4" />
                                  </Button>
                                </div>
                              </div>
                            ))}
                          </div>
                        </div>
                      )}
                      
                      {/* Requester Information */}
                      {project.created_by && (
                        <div className="mb-4">
                          <h4 className="text-sm font-medium text-muted-foreground mb-2">Project Requester</h4>
                          <div className="p-3 border rounded-lg bg-blue-50">
                            <div className="flex items-center justify-between">
                              <div>
                                <p className="font-medium text-sm">
                                  {project.created_by.first_name} {project.created_by.last_name}
                                </p>
                                <p className="text-xs text-muted-foreground">{project.created_by.email}</p>
                                <p className="text-xs text-muted-foreground">
                                  Created: {formatDate(project.created_at)}
                                </p>
                              </div>
                              <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => router.push(`/users/${project.created_by.id}`)}
                              >
                                <ExternalLink className="h-4 w-4" />
                              </Button>
                            </div>
                          </div>
                        </div>
                      )}
                      
                      {/* Tasker Information */}
                      {project.tasks && project.tasks.length > 0 && (
                        <div className="mb-4">
                          <h4 className="text-sm font-medium text-muted-foreground mb-2">Taskers</h4>
                          <div className="space-y-2">
                            {(() => {
                              const taskers = new Map();
                              project.tasks.forEach(task => {
                                if (task.users) {
                                  task.users.forEach(user => {
                                    if (!taskers.has(user.id)) {
                                      taskers.set(user.id, {
                                        ...user,
                                        taskCount: 0,
                                        completedTasks: 0,
                                        overdueTasks: 0
                                      });
                                    }
                                    const tasker = taskers.get(user.id);
                                    tasker.taskCount++;
                                    if (task.status?.title === 'Completed') {
                                      tasker.completedTasks++;
                                    } else if (task.end_date && new Date(task.end_date) < new Date()) {
                                      tasker.overdueTasks++;
                                    }
                                  });
                                }
                              });
                              return Array.from(taskers.values());
                            })().map((tasker) => (
                              <div key={tasker.id} className="p-3 border rounded-lg bg-green-50">
                                <div className="flex items-center justify-between">
                                  <div>
                                    <p className="font-medium text-sm">
                                      {tasker.first_name} {tasker.last_name}
                                    </p>
                                    {tasker.email && (
                                      <p className="text-xs text-muted-foreground">{tasker.email}</p>
                                    )}
                                    <div className="flex items-center gap-4 text-xs text-muted-foreground mt-1">
                                      <span>{tasker.taskCount} tasks assigned</span>
                                      <span>{tasker.completedTasks} completed</span>
                                      {tasker.overdueTasks > 0 && (
                                        <span className="text-red-600">{tasker.overdueTasks} overdue</span>
                                      )}
                                    </div>
                                  </div>
                                  <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => router.push(`/users/${tasker.id}`)}
                                  >
                                    <ExternalLink className="h-4 w-4" />
                                  </Button>
                                </div>
                              </div>
                            ))}
                          </div>
                        </div>
                      )}
                      
                      {/* Project Timeline */}
                      {(project.start_date || project.end_date) && (
                        <div className="mb-4">
                          <h4 className="text-sm font-medium text-muted-foreground mb-2">Timeline</h4>
                          <div className="p-3 border rounded-lg bg-yellow-50">
                            <div className="flex items-center gap-4 text-sm">
                              {project.start_date && (
                                <div>
                                  <span className="text-muted-foreground">Start:</span>
                                  <span className="ml-1 font-medium">{formatDate(project.start_date)}</span>
                                </div>
                              )}
                              {project.end_date && (
                                <div>
                                  <span className="text-muted-foreground">End:</span>
                                  <span className="ml-1 font-medium">{formatDate(project.end_date)}</span>
                                </div>
                              )}
                            </div>
                          </div>
                        </div>
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

export default function ProjectManagementPage() {
  return (
    <Suspense fallback={
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading projects...</p>
          </div>
        </div>
      </MainLayout>
    }>
      <ProjectManagementPageContent />
    </Suspense>
  );
}
