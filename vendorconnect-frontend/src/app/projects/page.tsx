'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import apiClient from '@/lib/api-client';
import { Plus, Search, Calendar, Users, CheckCircle, Clock, AlertCircle, DollarSign } from 'lucide-react';
import { format } from 'date-fns';

interface Project {
  id: number;
  title: string;
  description?: string;
  clients?: Array<{
    id: number;
    name: string;
  }>;
  status?: string;
  start_date?: string;
  end_date?: string;
  budget?: number;
  users?: Array<{
    id: number;
    first_name: string;
    last_name: string;
  }>;
  progress?: number;
  tasks_count?: number;
  completed_tasks?: number;
  team_members_count?: number;
}

export default function ProjectsPage() {
  const router = useRouter();
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState<string>('all');

  useEffect(() => {
    fetchProjects();
  }, []);

  // Debounced search effect
  useEffect(() => {
    const timeoutId = setTimeout(() => {
      fetchProjects(searchTerm);
    }, 500); // 500ms delay

    return () => clearTimeout(timeoutId);
  }, [searchTerm]);

  const fetchProjects = async (searchQuery = '') => {
    try {
      const params = new URLSearchParams();
      if (searchQuery) {
        params.append('search', searchQuery);
      }
      
      const response = await apiClient.get(`/projects?${params.toString()}`);
      // Handle paginated response
      const projectData = response.data.data?.data || response.data.data || [];
      setProjects(Array.isArray(projectData) ? projectData : []);
    } catch (error) {
      console.error('Failed to fetch projects:', error);
      setProjects([]);
    } finally {
      setLoading(false);
    }
  };

  const getStatusIcon = (status?: string) => {
    if (!status) return <Clock className="h-5 w-5 text-gray-500" />;
    
    switch (status.toLowerCase()) {
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

  const getStatusColor = (status?: string) => {
    if (!status) return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
    
    switch (status.toLowerCase()) {
      case 'completed':
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
      case 'in progress':
      case 'active':
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
      case 'on hold':
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
      default:
        return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
    }
  };

  const calculateProgress = (completedTasks: number, totalTasks: number) => {
    if (totalTasks === 0) return 0;
    return Math.round((completedTasks / totalTasks) * 100);
  };

  // Use projects directly since search is now server-side
  const filteredProjects = projects.filter(project => {
    const matchesStatus = 
      statusFilter === 'all' || 
      (project.status?.toLowerCase() || '') === statusFilter.toLowerCase();
    
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
      <div className="space-y-8">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Projects</h1>
            <p className="text-muted-foreground">Manage and track all your projects</p>
          </div>
          <Button onClick={() => router.push('/projects/new')}>
            <Plus className="mr-2 h-4 w-4" />
            New Project
          </Button>
        </div>

        {/* Search and Filters */}
        <Card>
          <CardContent className="p-4">
            <div className="flex gap-4">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                <Input
                  placeholder="Search projects..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-10"
                />
              </div>
              <select
                value={statusFilter}
                onChange={(e) => setStatusFilter(e.target.value)}
                className="px-4 py-2 border rounded-md bg-background"
              >
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="on hold">On Hold</option>
              </select>
            </div>
          </CardContent>
        </Card>

        {/* Projects Grid */}
        {filteredProjects.length > 0 ? (
          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {filteredProjects.map((project) => (
              <Card 
                key={project.id} 
                className="hover:shadow-lg transition-shadow cursor-pointer"
                onClick={() => router.push(`/projects/${project.id}`)}
              >
                <CardHeader>
                  <div className="flex justify-between items-start">
                    <div className="space-y-1">
                      <CardTitle className="text-xl">{project.title || 'Unnamed Project'}</CardTitle>
                      <CardDescription>{project.clients?.[0]?.name || 'No Client'}</CardDescription>
                    </div>
                    <div className="flex items-center gap-2">
                      {getStatusIcon(project.status)}
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(project.status)}`}>
                        {project.status || 'No Status'}
                      </span>
                    </div>
                  </div>
                </CardHeader>
                <CardContent className="space-y-4">
                  {project.description && (
                    <p className="text-sm text-muted-foreground line-clamp-2">{project.description}</p>
                  )}

                  {/* Progress Bar */}
                  <div className="space-y-2">
                    <div className="flex justify-between text-sm">
                      <span className="text-muted-foreground">Progress</span>
                      <span className="font-medium">{calculateProgress(project.completed_tasks || 0, project.tasks_count || 0)}%</span>
                    </div>
                    <div className="w-full bg-secondary rounded-full h-2">
                      <div
                        className="bg-primary rounded-full h-2 transition-all"
                        style={{ width: `${calculateProgress(project.completed_tasks || 0, project.tasks_count || 0)}%` }}
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-2 gap-4 text-sm">
                    {project.start_date && (
                      <div className="space-y-1">
                        <p className="text-muted-foreground">Start Date</p>
                        <p className="font-medium">{format(new Date(project.start_date), 'MMM dd, yyyy')}</p>
                      </div>
                    )}
                    {project.end_date && (
                      <div className="space-y-1">
                        <p className="text-muted-foreground">End Date</p>
                        <p className="font-medium">{format(new Date(project.end_date), 'MMM dd, yyyy')}</p>
                      </div>
                    )}
                  </div>

                  <div className="flex items-center justify-between pt-4 border-t">
                    <div className="flex items-center space-x-4 text-sm">
                      <div className="flex items-center">
                        <Users className="mr-1 h-4 w-4 text-muted-foreground" />
                        <span>{project.team_members_count || project.users?.length || 0}</span>
                      </div>
                      <div className="flex items-center">
                        <CheckCircle className="mr-1 h-4 w-4 text-muted-foreground" />
                        <span>{project.completed_tasks || 0}/{project.tasks_count || 0}</span>
                      </div>
                    </div>
                    {project.budget && (
                      <div className="flex items-center text-sm font-medium">
                        <DollarSign className="h-4 w-4" />
                        {project.budget.toLocaleString()}
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
              {searchTerm || statusFilter !== 'all' 
                ? 'No projects found matching your criteria.' 
                : 'No projects available. Create your first project to get started!'}
            </p>
          </div>
        )}
      </div>
    </MainLayout>
  );
}