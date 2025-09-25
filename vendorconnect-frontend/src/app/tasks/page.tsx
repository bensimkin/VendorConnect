'use client';

import { useState, useEffect, Suspense } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import apiClient from '@/lib/api-client';
import { Plus, Search, Filter, Calendar, User, Tag, CheckCircle, Clock, AlertCircle, X } from 'lucide-react';
import { format } from 'date-fns';

interface Task {
  id: number;
  title: string;
  description?: string;
  note?: string;
  deliverable_quantity?: number;
  deliverables_count?: number;
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
  // clients?: Array<{
  //   id: number;
  //   name: string;
  // }>;
  // NOTE: No direct client-task relationship exists in current schema
  project?: {
    id: number;
    title: string;
    description?: string;
    admin_id?: number | null;
    workspace_id?: number;
    status_id?: number;
    priority_id?: number | null;
    budget?: number | null;
    start_date?: string;
    end_date?: string;
    created_by?: number;
    is_favorite?: number;
    task_accessibility?: string;
    note?: string | null;
    created_at?: string;
    updated_at?: string;
  };
  template?: {
    id: number;
    title: string;
    standard_brief?: string;
    description?: string;
    deliverable_quantity?: number;
  };
  start_date?: string;
  end_date?: string;
  created_at: string;
  tags?: Array<{ id: number; name: string }>;
}

interface FilterOption {
  id: number;
  title?: string;
  name?: string;
}

function TasksPageContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);
  
  // Initialize filter states from URL params or localStorage
  const getInitialFilterValue = (key: string, defaultValue: string = 'all') => {
    const urlValue = searchParams.get(key);
    if (urlValue !== null) return urlValue;
    
    // Fallback to localStorage
    if (typeof window !== 'undefined') {
      const stored = localStorage.getItem(`tasks_filter_${key}`);
      return stored || defaultValue;
    }
    return defaultValue;
  };

  const [searchTerm, setSearchTerm] = useState(getInitialFilterValue('search', ''));
  const [selectedStatus, setSelectedStatus] = useState(getInitialFilterValue('status'));
  const [selectedProject, setSelectedProject] = useState(getInitialFilterValue('project'));
  const [selectedClient, setSelectedClient] = useState(getInitialFilterValue('client'));
  const [selectedTaskType, setSelectedTaskType] = useState(getInitialFilterValue('taskType'));
  const [selectedPriority, setSelectedPriority] = useState(getInitialFilterValue('priority'));
  
  // Filter options
  const [projects, setProjects] = useState<FilterOption[]>([]);
  const [clients, setClients] = useState<FilterOption[]>([]);
  const [taskTypes, setTaskTypes] = useState<FilterOption[]>([]);
  const [statuses, setStatuses] = useState<FilterOption[]>([]);
  const [priorities, setPriorities] = useState<FilterOption[]>([]);
  
  // Filter panel state
  const [showFilters, setShowFilters] = useState(false);

  useEffect(() => {
    fetchTasks();
    fetchFilterOptions();
  }, []);

  // Debounced search effect
  useEffect(() => {
    const timeoutId = setTimeout(() => {
      fetchTasks(searchTerm);
    }, 500); // 500ms delay

    return () => clearTimeout(timeoutId);
  }, [searchTerm]);

  // Refetch tasks when filters change
  useEffect(() => {
    fetchTasks(searchTerm);
  }, [selectedStatus, selectedProject, selectedClient, selectedTaskType, selectedPriority]);

  const fetchFilterOptions = async () => {
    try {
      const [projectsRes, clientsRes, taskTypesRes, statusesRes, prioritiesRes] = await Promise.all([
        apiClient.get('/projects?per_page=all'),
        apiClient.get('/clients?per_page=all'),
        apiClient.get('/task-types?per_page=all'),
        apiClient.get('/statuses?per_page=all'),
        apiClient.get('/priorities?per_page=all')
      ]);

      setProjects(projectsRes.data?.data || []);
      setClients(clientsRes.data?.data || []);
      setTaskTypes(taskTypesRes.data?.data || []);
      setStatuses(statusesRes.data?.data || []);
      setPriorities(prioritiesRes.data?.data || []);
    } catch (error) {
      console.error('Failed to fetch filter options:', error);
    }
  };

  const fetchTasks = async (searchQuery = '') => {
    try {
      const params = new URLSearchParams();
      if (searchQuery) {
        params.append('search', searchQuery);
      }
      if (selectedStatus !== 'all') {
        params.append('status_id', selectedStatus);
      }
      if (selectedProject !== 'all') {
        params.append('project_id', selectedProject);
      }
      if (selectedClient !== 'all') {
        params.append('client_id', selectedClient);
      }
      if (selectedTaskType !== 'all') {
        params.append('task_type_id', selectedTaskType);
      }
      if (selectedPriority !== 'all') {
        params.append('priority_id', selectedPriority);
      }

      const response = await apiClient.get(`/tasks?${params.toString()}`);
      // Handle varying pagination structures
      const taskData = response.data?.data?.data || response.data?.data || response.data || [];
      setTasks(Array.isArray(taskData) ? taskData : []);
    } catch (error) {
      console.error('Failed to fetch tasks:', error);
      setTasks([]);
    } finally {
      setLoading(false);
    }
  };

  const getStatusIcon = (statusName?: string) => {
    if (!statusName) return <Clock className="h-4 w-4 text-gray-500" />;
    
    switch (statusName.toLowerCase()) {
      case 'completed':
        return <CheckCircle className="h-4 w-4 text-green-500" />;
      case 'in progress':
        return <Clock className="h-4 w-4 text-blue-500" />;
      case 'pending':
        return <AlertCircle className="h-4 w-4 text-yellow-500" />;
      case 'submitted':
        return <CheckCircle className="h-4 w-4 text-purple-500" />;
      case 'archive':
        return <X className="h-4 w-4 text-gray-400" />;
      default:
        return <Clock className="h-4 w-4 text-gray-500" />;
    }
  };

  // Use tasks directly since filtering is now server-side
  const filteredTasks = tasks;

  // Function to update URL and localStorage
  const updateFilterPersistence = (key: string, value: string) => {
    // Update localStorage
    if (typeof window !== 'undefined') {
      localStorage.setItem(`tasks_filter_${key}`, value);
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
    setSelectedProject('all');
    setSelectedClient('all');
    setSelectedTaskType('all');
    setSelectedPriority('all');
    setSearchTerm('');
    
    // Clear all persistent storage
    if (typeof window !== 'undefined') {
      localStorage.removeItem('tasks_filter_status');
      localStorage.removeItem('tasks_filter_project');
      localStorage.removeItem('tasks_filter_client');
      localStorage.removeItem('tasks_filter_taskType');
      localStorage.removeItem('tasks_filter_priority');
      localStorage.removeItem('tasks_filter_search');
    }
    
    // Clear URL params
    router.replace(window.location.pathname, { scroll: false });
  };

  const hasActiveFilters = selectedStatus !== 'all' || 
    selectedProject !== 'all' || 
    selectedClient !== 'all' || 
    selectedTaskType !== 'all' || 
    selectedPriority !== 'all' || 
    searchTerm !== '';



  const formatDate = (dateString?: string) => {
    if (!dateString) return 'No date set';
    try {
      return format(new Date(dateString), 'MMM dd, yyyy');
    } catch {
      return 'Invalid date';
    }
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading tasks...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="space-y-8">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Tasks</h1>
            <p className="text-muted-foreground">Manage and track all your tasks</p>
          </div>
          <Button onClick={() => router.push('/tasks/new')}>
            <Plus className="mr-2 h-4 w-4" />
            New Task
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
                    placeholder="Search tasks..."
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
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
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

                  {/* Project Filter */}
                  <div className="space-y-2">
                    <label className="text-sm font-medium">Project</label>
                    <Select value={selectedProject} onValueChange={(value) => {
                      setSelectedProject(value);
                      updateFilterPersistence('project', value);
                    }}>
                      <SelectTrigger>
                        <SelectValue placeholder="All Projects" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">All Projects</SelectItem>
                        {projects.map((project) => (
                          <SelectItem key={project.id} value={project.id.toString()}>
                            {project.title || project.name}
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

                  {/* Task Type Filter */}
                  <div className="space-y-2">
                    <label className="text-sm font-medium">Task Type</label>
                    <Select value={selectedTaskType} onValueChange={(value) => {
                      setSelectedTaskType(value);
                      updateFilterPersistence('taskType', value);
                    }}>
                      <SelectTrigger>
                        <SelectValue placeholder="All Task Types" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">All Task Types</SelectItem>
                        {taskTypes.map((taskType) => (
                          <SelectItem key={taskType.id} value={taskType.id.toString()}>
                            {taskType.title || taskType.name}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>

                  {/* Priority Filter */}
                  <div className="space-y-2">
                    <label className="text-sm font-medium">Priority</label>
                    <Select value={selectedPriority} onValueChange={(value) => {
                      setSelectedPriority(value);
                      updateFilterPersistence('priority', value);
                    }}>
                      <SelectTrigger>
                        <SelectValue placeholder="All Priorities" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">All Priorities</SelectItem>
                        {priorities.map((priority) => (
                          <SelectItem key={priority.id} value={priority.id.toString()}>
                            {priority.title || priority.name}
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
                  {selectedProject !== 'all' && (
                    <Badge variant="secondary" className="flex items-center gap-1">
                      Project: {projects.find(p => p.id.toString() === selectedProject)?.title || selectedProject}
                      <X 
                        className="h-3 w-3 cursor-pointer" 
                        onClick={() => {
                          setSelectedProject('all');
                          updateFilterPersistence('project', 'all');
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
                  {selectedTaskType !== 'all' && (
                    <Badge variant="secondary" className="flex items-center gap-1">
                      Task Type: {taskTypes.find(t => t.id.toString() === selectedTaskType)?.title || selectedTaskType}
                      <X 
                        className="h-3 w-3 cursor-pointer" 
                        onClick={() => {
                          setSelectedTaskType('all');
                          updateFilterPersistence('taskType', 'all');
                        }}
                      />
                    </Badge>
                  )}
                  {selectedPriority !== 'all' && (
                    <Badge variant="secondary" className="flex items-center gap-1">
                      Priority: {priorities.find(p => p.id.toString() === selectedPriority)?.title || selectedPriority}
                      <X 
                        className="h-3 w-3 cursor-pointer" 
                        onClick={() => {
                          setSelectedPriority('all');
                          updateFilterPersistence('priority', 'all');
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

        {/* Tasks Grid */}
        {filteredTasks.length > 0 ? (
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {filteredTasks.map((task) => (
              <Card 
                key={task.id} 
                className="hover:shadow-lg transition-shadow cursor-pointer"
                onClick={() => router.push(`/tasks/${task.id}`)}
              >
                <CardHeader>
                  <div className="flex justify-between items-start">
                    <div className="space-y-1 flex-1">
                      <CardTitle className="text-lg line-clamp-2">
                        {task.title || 'Untitled Task'}
                      </CardTitle>
                      {task.description && (
                        <CardDescription className="line-clamp-2">
                          {task.description}
                        </CardDescription>
                      )}
                    </div>
                    {getStatusIcon(task.status?.title)}
                  </div>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    {/* Project */}
                    {task.project && (
                      <div className="flex items-center gap-2 text-sm">
                        <Tag className="h-4 w-4 text-muted-foreground" />
                        <span className="truncate text-blue-600">
                          {task.project.title || 'Unnamed Project'}
                        </span>
                      </div>
                    )}

                    {/* Clients - Removed: No direct client-task relationship exists */}
                    {/* Clients are related to projects, not directly to tasks */}

                    {/* Assigned Users */}
                    {task.users && task.users.length > 0 && (
                      <div className="flex items-center gap-2 text-sm">
                        <User className="h-4 w-4 text-muted-foreground" />
                        <span className="truncate">
                          {task.users.map(user => 
                            `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'Unknown User'
                          ).join(', ')}
                        </span>
                      </div>
                    )}
                    
                    {/* Due Date */}
                    {task.end_date && (
                      <div className="flex items-center gap-2 text-sm">
                        <Calendar className="h-4 w-4 text-muted-foreground" />
                        <span>Due {formatDate(task.end_date)}</span>
                      </div>
                    )}

                    {/* Template Information */}
                    {task.template && (
                      <div className="flex items-center gap-2 text-sm">
                        <span className="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                          {task.template.title}
                        </span>
                      </div>
                    )}

                    {/* Notes */}
                    {task.note && (
                      <div className="text-sm text-muted-foreground line-clamp-2">
                        <span className="font-medium">Notes:</span> {task.note}
                      </div>
                    )}

                    {/* Quantity */}
                    {task.deliverable_quantity && task.deliverable_quantity > 1 && (
                      <div className="flex items-center gap-2 text-sm">
                        <span className="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                          Qty: {task.deliverable_quantity}
                        </span>
                      </div>
                    )}
                    
                    {/* Deliverables Count */}
                    <div className="flex items-center gap-2 text-sm">
                      <span className="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">
                        📎 {task.deliverables_count || 0} deliverable{(task.deliverables_count || 0) !== 1 ? 's' : ''}
                      </span>
                    </div>
                    
                    {/* Priority and Status */}
                    <div className="flex items-center gap-2 flex-wrap">
                      {task.priority && (
                        <span className="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                          {task.priority.title || 'No Priority'}
                        </span>
                      )}
                      {task.status && (
                        <span className="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                          {task.status.title || 'No Status'}
                        </span>
                      )}
                    </div>
                    
                    {/* Tags */}
                    {task.tags && task.tags.length > 0 && (
                      <div className="flex items-center gap-2 flex-wrap">
                        <Tag className="h-3 w-3 text-muted-foreground" />
                        {task.tags.map((tag) => (
                          <span key={tag.id} className="text-xs text-muted-foreground">
                            {tag.name || 'Unnamed Tag'}
                          </span>
                        ))}
                      </div>
                    )}
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <div className="text-center py-12">
            <p className="text-muted-foreground">
              {searchTerm || selectedStatus !== 'all' 
                ? 'No tasks found matching your criteria.' 
                : 'No tasks available. Create your first task to get started!'}
            </p>
          </div>
        )}
      </div>
    </MainLayout>
  );
}

export default function TasksPage() {
  return (
    <Suspense fallback={
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading tasks...</p>
          </div>
        </div>
      </MainLayout>
    }>
      <TasksPageContent />
    </Suspense>
  );
}