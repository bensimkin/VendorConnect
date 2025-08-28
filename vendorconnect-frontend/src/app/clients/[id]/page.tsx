'use client';

import React, { useState, useEffect } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { toast } from 'react-hot-toast';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, Trash2, Building, Mail, Phone, MapPin, Globe, Calendar, Briefcase, Users, Plus, Key } from 'lucide-react';
import { apiClient } from '@/lib/api-client';
import { format } from 'date-fns';
import { useAuthStore } from '@/lib/auth-store';
import { filterSensitiveClientData, hasAdminPrivileges } from '@/lib/utils/role-utils';

interface Client {
  id: number;
  name: string;
  email?: string;
  phone?: string;
  address?: string;
  company?: string;
  website?: string;
  notes?: string;
  status?: number;
  created_at: string;
  updated_at: string;
  // New fields
  city?: string;
  state?: string;
  country?: string;
  zip?: string;
  dob?: string;
  doj?: string;
}

interface Project {
  id: number;
  title: string;
  description?: string;
  status?: {
    name: string;
    color?: string;
  };
  start_date?: string;
  end_date?: string;
  created_at: string;
}

interface Task {
  id: number;
  title: string;
  description?: string;
  status?: {
    id: number;
    title: string;
    slug: string;
    name: string;
  };
  priority?: {
    id: number;
    title: string;
    slug: string;
    name: string;
  };
  created_at: string;
}

export default function ClientDetailPage() {
  const router = useRouter();
  const params = useParams();
  const { user } = useAuthStore();
  const clientId = params.id as string;

  const [client, setClient] = useState<Client | null>(null);
  const [projects, setProjects] = useState<Project[]>([]);
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchClientData();
  }, [clientId]);

  const fetchClientData = async () => {
    console.log('🔍 [DEBUG] Starting fetchClientData for clientId:', clientId);
    console.log('🔍 [DEBUG] Current user:', user);
    
    try {
      console.log('🔍 [DEBUG] Making API calls to fetch client data...');
      
      const [clientResponse, projectsResponse, tasksResponse] = await Promise.all([
        apiClient.get(`/clients/${clientId}`),
        apiClient.get(`/clients/${clientId}/projects`),
        apiClient.get(`/clients/${clientId}/tasks`)
      ]);

      console.log('🔍 [DEBUG] Client API Response:', clientResponse);
      console.log('🔍 [DEBUG] Projects API Response:', projectsResponse);
      console.log('🔍 [DEBUG] Tasks API Response:', tasksResponse);

      // Filter sensitive data based on user role
      const rawClient = clientResponse.data.data;
      console.log('🔍 [DEBUG] Raw client data:', rawClient);
      
      const filteredClient = filterSensitiveClientData(rawClient, user);
      console.log('🔍 [DEBUG] Filtered client data:', filteredClient);
      setClient(filteredClient);

      const projectsData = projectsResponse.data.data?.data || projectsResponse.data.data || [];
      const tasksData = tasksResponse.data.data || [];
      
      console.log('🔍 [DEBUG] Projects data to set:', projectsData);
      console.log('🔍 [DEBUG] Tasks data to set:', tasksData);
      
      setProjects(projectsData);
      setTasks(tasksData);
      
      console.log('🔍 [DEBUG] State updated successfully');
    } catch (error: any) {
      console.error('❌ [DEBUG] Failed to fetch client data:', error);
      console.error('❌ [DEBUG] Error response:', error.response);
      console.error('❌ [DEBUG] Error message:', error.message);
      console.error('❌ [DEBUG] Error stack:', error.stack);
      toast.error('Failed to load client data');
      router.push('/clients');
    } finally {
      setLoading(false);
      console.log('🔍 [DEBUG] Loading state set to false');
    }
  };

  const handleDeleteClient = async () => {
    if (!client) return;
    
    if (!confirm(`Are you sure you want to delete "${client.name}"? This action cannot be undone.`)) {
      return;
    }

    try {
      await apiClient.delete(`/clients/${clientId}`);
      toast.success('Client deleted successfully');
      router.push('/clients');
    } catch (error: any) {
      console.error('Failed to delete client:', error);
      toast.error('Failed to delete client');
    }
  };

  const getStatusBadge = (status?: number) => {
    if (status === 1) {
      return <Badge className="bg-green-100 text-green-800">Active</Badge>;
    }
    return <Badge className="bg-red-100 text-red-800">Inactive</Badge>;
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center min-h-screen">
          <div className="text-center">
            <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900 mx-auto"></div>
            <p className="mt-4 text-lg">Loading client...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  if (!client) {
    return (
      <MainLayout>
        <div className="text-center py-12">
          <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-2">Client not found</h3>
          <Button onClick={() => router.push('/clients')}>Back to Clients</Button>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-8">
          <div className="flex items-center space-x-4">
            <Button
              variant="ghost"
              onClick={() => router.push('/clients')}
              className="flex items-center space-x-2"
            >
              <ArrowLeft className="h-4 w-4" />
              <span>Back to Clients</span>
            </Button>
            <div>
              <h1 className="text-3xl font-bold text-gray-900 dark:text-white">{client.name}</h1>
              <p className="text-gray-600 dark:text-gray-400">Client Details</p>
            </div>
          </div>
          <div className="flex items-center space-x-2">
            <Button
              variant="outline"
              onClick={() => router.push(`/clients/${clientId}/edit`)}
            >
              <Edit className="h-4 w-4 mr-2" />
              Edit
            </Button>
            <Button
              variant="destructive"
              onClick={handleDeleteClient}
            >
              <Trash2 className="h-4 w-4 mr-2" />
              Delete
            </Button>
          </div>
        </div>

        <div className="grid gap-6 lg:grid-cols-3">
          {/* Main Content */}
          <div className="lg:col-span-2 space-y-6">
            {/* Contact Information */}
            <Card>
              <CardHeader>
                <CardTitle>Contact Information</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {/* Only show sensitive data to admin users */}
                  {hasAdminPrivileges(user) && client.email && (
                    <div className="flex items-center space-x-2">
                      <Mail className="h-4 w-4 text-muted-foreground" />
                      <div>
                        <p className="text-sm font-medium">Email</p>
                        <p className="text-sm text-muted-foreground">{client.email}</p>
                      </div>
                    </div>
                  )}
                  {hasAdminPrivileges(user) && client.phone && (
                    <div className="flex items-center space-x-2">
                      <Phone className="h-4 w-4 text-muted-foreground" />
                      <div>
                        <p className="text-sm font-medium">Phone</p>
                        <p className="text-sm text-muted-foreground">{client.phone}</p>
                      </div>
                    </div>
                  )}
                  {hasAdminPrivileges(user) && client.website && (
                    <div className="flex items-center space-x-2">
                      <Globe className="h-4 w-4 text-muted-foreground" />
                      <div>
                        <p className="text-sm font-medium">Website</p>
                        <a
                          href={client.website}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="text-sm text-blue-600 hover:text-blue-800"
                        >
                          {client.website}
                        </a>
                      </div>
                    </div>
                  )}
                  <div className="flex items-center space-x-2">
                    <Calendar className="h-4 w-4 text-muted-foreground" />
                    <div>
                      <p className="text-sm font-medium">Joined</p>
                      <p className="text-sm text-muted-foreground">
                        {format(new Date(client.created_at), 'MMM d, yyyy')}
                      </p>
                    </div>
                  </div>
                </div>

                {/* Only show address to admin users */}
                {hasAdminPrivileges(user) && client.address && (
                  <div className="flex items-start space-x-2">
                    <MapPin className="h-4 w-4 text-muted-foreground mt-0.5" />
                    <div>
                      <p className="text-sm font-medium">Address</p>
                      <p className="text-sm text-muted-foreground">{client.address}</p>
                      {(client.city || client.state || client.country) && (
                        <p className="text-sm text-muted-foreground">
                          {[client.city, client.state, client.country].filter(Boolean).join(', ')}
                        </p>
                      )}
                    </div>
                  </div>
                )}

                {/* Show non-sensitive data to all users */}
                <div className="flex items-center space-x-2">
                  <Building className="h-4 w-4 text-muted-foreground" />
                  <div>
                    <p className="text-sm font-medium">Company</p>
                    <p className="text-sm text-muted-foreground">{client.company || 'Not specified'}</p>
                  </div>
                </div>

                <div className="flex items-center space-x-2">
                  <Key className="h-4 w-4 text-muted-foreground" />
                  <div>
                    <p className="text-sm font-medium">Status</p>
                    <div className="mt-1">{getStatusBadge(client.status)}</div>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Only show notes to admin users */}
            {hasAdminPrivileges(user) && client.notes && (
              <Card>
                <CardHeader>
                  <CardTitle>Notes</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-sm text-muted-foreground">{client.notes}</p>
                </CardContent>
              </Card>
            )}

            {/* Projects */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Briefcase className="h-5 w-5" />
                  Projects ({projects.length})
                </CardTitle>
              </CardHeader>
              <CardContent>
                {projects.length > 0 ? (
                  <div className="space-y-4">
                    {projects.map((project) => (
                      <div key={project.id} className="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                          <h3 className="font-medium">{project.title}</h3>
                          {project.description && (
                            <p className="text-sm text-muted-foreground mt-1">
                              {project.description}
                            </p>
                          )}
                          <div className="flex items-center space-x-4 mt-2 text-xs text-muted-foreground">
                            <span>Created {format(new Date(project.created_at), 'MMM d, yyyy')}</span>
                            {project.start_date && (
                              <span>Started {format(new Date(project.start_date), 'MMM d, yyyy')}</span>
                            )}
                          </div>
                        </div>
                        <div className="flex items-center space-x-2">
                          {project.status && (
                            <Badge
                              style={{
                                backgroundColor: project.status.color ? `${project.status.color}20` : undefined,
                                color: project.status.color || undefined,
                              }}
                            >
                              {project.status.name}
                            </Badge>
                          )}
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => router.push(`/projects/${project.id}`)}
                          >
                            View
                          </Button>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <Briefcase className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                    <p className="text-gray-600 dark:text-gray-400">No projects found for this client</p>
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Tasks */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Users className="h-5 w-5" />
                  Tasks ({tasks.length})
                </CardTitle>
              </CardHeader>
              <CardContent>
                {tasks.length > 0 ? (
                  <div className="space-y-4">
                    {tasks.map((task) => (
                      <div key={task.id} className="flex items-center justify-between p-4 border rounded-lg">
                        <div className="flex-1">
                          <h3 className="font-medium">{task.title}</h3>
                          {task.description && (
                            <p className="text-sm text-muted-foreground mt-1">
                              {task.description}
                            </p>
                          )}
                          <div className="flex gap-2 mt-2">
                            {task.status && (
                              <Badge variant="outline" className="text-xs">
                                {task.status.name}
                              </Badge>
                            )}
                            {task.priority && (
                              <Badge variant="outline" className="text-xs">
                                {task.priority.name}
                              </Badge>
                            )}
                          </div>
                        </div>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => router.push(`/tasks/${task.id}`)}
                        >
                          View
                        </Button>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <Users className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                    <p className="text-gray-600 dark:text-gray-400">No tasks found for this client</p>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Quick Actions */}
            <Card>
              <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <Button
                  variant="outline"
                  className="w-full justify-start"
                  onClick={() => router.push(`/projects/new?client_id=${clientId}`)}
                >
                  <Plus className="h-4 w-4 mr-2" />
                  New Project
                </Button>
                <Button
                  variant="outline"
                  className="w-full justify-start"
                  onClick={() => router.push(`/tasks/new?client_id=${clientId}`)}
                >
                  <Plus className="h-4 w-4 mr-2" />
                  New Task
                </Button>
              </CardContent>
            </Card>

            {/* Statistics */}
            <Card>
              <CardHeader>
                <CardTitle>Statistics</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex items-center justify-between">
                  <span className="text-sm font-medium">Total Projects</span>
                  <span className="text-sm text-muted-foreground">{projects.length}</span>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-sm font-medium">Total Tasks</span>
                  <span className="text-sm text-muted-foreground">{tasks.length}</span>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-sm font-medium">Member Since</span>
                  <span className="text-sm text-muted-foreground">
                    {format(new Date(client.created_at), 'MMM yyyy')}
                  </span>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </MainLayout>
  );
}
