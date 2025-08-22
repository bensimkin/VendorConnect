'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import apiClient from '@/lib/api-client';
import { Plus, Search, Filter, Calendar, User, Tag, CheckCircle, Clock, AlertCircle } from 'lucide-react';
import { format } from 'date-fns';

interface Task {
  id: number;
  title: string;
  description?: string;
  status?: {
    id: number;
    name: string;
    color?: string;
  };
  priority?: {
    id: number;
    name: string;
    color?: string;
  };
  users?: Array<{
    id: number;
    first_name: string;
    last_name: string;
  }>;
  start_date?: string;
  end_date?: string;
  created_at: string;
  tags?: Array<{ id: number; name: string }>;
}

export default function TasksPage() {
  const router = useRouter();
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedStatus, setSelectedStatus] = useState<string>('all');

  useEffect(() => {
    fetchTasks();
  }, []);

  const fetchTasks = async () => {
    try {
      const response = await apiClient.get('/tasks');
      // Handle paginated response
      const taskData = response.data.data?.data || response.data.data || [];
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
      default:
        return <Clock className="h-4 w-4 text-gray-500" />;
    }
  };

  const filteredTasks = tasks.filter(task => {
    const matchesSearch = 
      (task.title?.toLowerCase() || '').includes(searchTerm.toLowerCase()) ||
      (task.description?.toLowerCase() || '').includes(searchTerm.toLowerCase());
    
    const matchesStatus = 
      selectedStatus === 'all' || 
      task.status?.name === selectedStatus;
    
    return matchesSearch && matchesStatus;
  });

  const getStatusColor = (color?: string) => {
    return color || '#6b7280'; // Default gray color
  };

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
            <div className="flex gap-4 items-center">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                <Input
                  placeholder="Search tasks..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-10"
                />
              </div>
              <Button variant="outline">
                <Filter className="mr-2 h-4 w-4" />
                Filters
              </Button>
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
                    {getStatusIcon(task.status?.name)}
                  </div>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
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
                    
                    {/* Priority and Status */}
                    <div className="flex items-center gap-2 flex-wrap">
                      {task.priority && (
                        <span
                          className="px-2 py-1 text-xs rounded-full"
                          style={{
                            backgroundColor: `${getStatusColor(task.priority.color)}20`,
                            color: getStatusColor(task.priority.color),
                          }}
                        >
                          {task.priority.name || 'No Priority'}
                        </span>
                      )}
                      {task.status && (
                        <span
                          className="px-2 py-1 text-xs rounded-full"
                          style={{
                            backgroundColor: `${getStatusColor(task.status.color)}20`,
                            color: getStatusColor(task.status.color),
                          }}
                        >
                          {task.status.name || 'No Status'}
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