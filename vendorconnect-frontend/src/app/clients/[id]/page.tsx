'use client';

import React, { useState, useEffect } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { toast } from 'react-hot-toast';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, Trash2, Building, Mail, Phone, MapPin, Globe, Calendar, Briefcase, Users, Plus, Key, FileText, Image, Presentation, Archive, Download, Eye } from 'lucide-react';
import { apiClient } from '@/lib/api-client';
import { format } from 'date-fns';
import { useAuthStore } from '@/lib/auth-store';
import { filterSensitiveClientData, hasAdminPrivileges } from '@/lib/utils/role-utils';
import ClientBriefSection from '@/components/clients/ClientBriefSection';

interface Client {
  id: number;
  name: string;
  first_name?: string;
  last_name?: string;
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
    title: string;  // FIXED: Use primary database field
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
    title: string;  // FIXED: Use primary database field
    slug: string;
  };
  priority?: {
    id: number;
    title: string;  // FIXED: Use primary database field
    slug: string;
  };
  created_at: string;
}

interface Portfolio {
  id: number;
  title: string;
  description?: string;
  deliverable_type: 'design' | 'document' | 'presentation' | 'other';
  status: 'completed' | 'in_progress' | 'review';
  created_at: string;
  completed_at?: string;
  task?: {
    id: number;
    title: string;
  };
  project?: {
    id: number;
    title: string;
  };
  media: Array<{
    id: number;
    file_name: string;
    mime_type: string;
    size: number;
    original_url: string;
    created_at: string;
  }>;
}

export default function ClientDetailPage() {
  const router = useRouter();
  const params = useParams();
  const { user } = useAuthStore();
  const clientId = params.id as string;

  const [client, setClient] = useState<Client | null>(null);
  const [projects, setProjects] = useState<Project[]>([]);
  const [tasks, setTasks] = useState<Task[]>([]);
  const [portfolios, setPortfolios] = useState<Portfolio[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchClientData();
  }, [clientId]);

  const fetchClientData = async () => {
    console.log('🔍 [DEBUG] Starting fetchClientData for clientId:', clientId);
    console.log('🔍 [DEBUG] Current user:', user);
    
    try {
      console.log('🔍 [DEBUG] Making API calls to fetch client data...');
      
      const [clientResponse, projectsResponse, tasksResponse, portfoliosResponse] = await Promise.all([
        apiClient.get(`/clients/${clientId}`),
        apiClient.get(`/clients/${clientId}/projects`),
        apiClient.get(`/clients/${clientId}/tasks`),
        apiClient.get(`/clients/${clientId}/portfolio`)
      ]);

      console.log('🔍 [DEBUG] Client API Response:', clientResponse);
      console.log('🔍 [DEBUG] Projects API Response:', projectsResponse);
      console.log('🔍 [DEBUG] Tasks API Response:', tasksResponse);
      console.log('🔍 [DEBUG] Portfolios API Response:', portfoliosResponse);

      // Filter sensitive data based on user role
      const rawClient = clientResponse.data.data;
      console.log('🔍 [DEBUG] Raw client data:', rawClient);
      
      const filteredClient = filterSensitiveClientData(rawClient, user);
      console.log('🔍 [DEBUG] Filtered client data:', filteredClient);
      setClient(filteredClient);

      const projectsData = projectsResponse.data.data?.data || projectsResponse.data.data || [];
      const tasksData = tasksResponse.data.data?.data || tasksResponse.data.data || [];
      const portfoliosData = portfoliosResponse.data.data?.data || portfoliosResponse.data.data || [];
      
      console.log('🔍 [DEBUG] Projects data to set:', projectsData);
      console.log('🔍 [DEBUG] Tasks data to set:', tasksData);
      console.log('🔍 [DEBUG] Portfolios data to set:', portfoliosData);
      
      setProjects(projectsData);
      setTasks(tasksData);
      setPortfolios(portfoliosData);
      
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
    
    const displayName = `${client.first_name || ''} ${client.last_name || ''}`.trim();
    if (!confirm(`Are you sure you want to delete "${displayName}"? This action cannot be undone.`)) {
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

  const getDeliverableTypeIcon = (type: string) => {
    switch (type) {
      case 'design':
        return <Image className="h-5 w-5" />;
      case 'document':
        return <FileText className="h-5 w-5" />;
      case 'presentation':
        return <Presentation className="h-5 w-5" />;
      default:
        return <Archive className="h-5 w-5" />;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed':
        return 'bg-green-100 text-green-800';
      case 'in_progress':
        return 'bg-blue-100 text-blue-800';
      case 'review':
        return 'bg-yellow-100 text-yellow-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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
              <h1 className="text-3xl font-bold text-gray-900 dark:text-white">{`${client.first_name || ''} ${client.last_name || ''}`.trim()}</h1>
              <p className="text-gray-600 dark:text-gray-400">Client Details</p>
            </div>
          </div>
          <div className="flex items-center space-x-2">
            <Button
              variant="outline"
              onClick={() => router.push(`/projects/new?client_id=${clientId}`)}
            >
              <Plus className="h-4 w-4 mr-2" />
              New Project
            </Button>
            <Button
              variant="outline"
              onClick={() => router.push(`/tasks/new?client_id=${clientId}`)}
            >
              <Plus className="h-4 w-4 mr-2" />
              New Task
            </Button>
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

            {/* Client Brief & Files */}
            <ClientBriefSection clientId={client.id} />

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
                              {project.status.title}
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
                                {task.status.title}
                              </Badge>
                            )}
                            {task.priority && (
                              <Badge variant="outline" className="text-xs">
                                {task.priority.title}
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

            {/* Portfolio Items */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <FileText className="h-5 w-5" />
                  Portfolio Items ({portfolios.length})
                </CardTitle>
              </CardHeader>
              <CardContent>
                {portfolios.length > 0 ? (
                  <div className="space-y-4">
                    {portfolios.map((portfolio) => (
                      <div key={portfolio.id} className="border rounded-lg p-4 space-y-3">
                        <div className="flex items-start justify-between">
                          <div className="flex-1">
                            <div className="flex items-center gap-2 mb-2">
                              {getDeliverableTypeIcon(portfolio.deliverable_type)}
                              <h3 className="font-medium">{portfolio.title}</h3>
                            </div>
                            {portfolio.description && (
                              <p className="text-sm text-muted-foreground mb-2">
                                {portfolio.description}
                              </p>
                            )}
                            <div className="flex gap-2 mb-2">
                              <Badge className={getStatusColor(portfolio.status)}>
                                {portfolio.status.replace('_', ' ')}
                              </Badge>
                              {portfolio.task && (
                                <Badge variant="outline" className="text-xs">
                                  Task: {portfolio.task.title}
                                </Badge>
                              )}
                              {portfolio.project && (
                                <Badge variant="outline" className="text-xs">
                                  Project: {portfolio.project.title}
                                </Badge>
                              )}
                            </div>
                            <div className="text-xs text-muted-foreground">
                              Created {format(new Date(portfolio.created_at), 'MMM d, yyyy')}
                              {portfolio.completed_at && (
                                <span> • Completed {format(new Date(portfolio.completed_at), 'MMM d, yyyy')}</span>
                              )}
                            </div>
                          </div>
                          <div className="flex gap-2">
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => router.push(`/portfolio/${portfolio.id}`)}
                            >
                              <Eye className="h-4 w-4 mr-1" />
                              View
                            </Button>
                          </div>
                        </div>
                        
                        {/* Files */}
                        {portfolio.media && portfolio.media.length > 0 && (
                          <div className="border-t pt-3">
                            <h4 className="text-sm font-medium mb-2">Files ({portfolio.media.length})</h4>
                            <div className="space-y-2">
                              {portfolio.media.slice(0, 3).map((file) => (
                                <div key={file.id} className="flex items-center justify-between text-sm">
                                  <div className="flex items-center gap-2">
                                    <FileText className="h-4 w-4 text-muted-foreground" />
                                    <span className="truncate">{file.file_name}</span>
                                    <span className="text-muted-foreground">({formatFileSize(file.size)})</span>
                                  </div>
                                  <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => window.open(`/api/files/${file.id}`, '_blank')}
                                  >
                                    <Download className="h-4 w-4" />
                                  </Button>
                                </div>
                              ))}
                              {portfolio.media.length > 3 && (
                                <p className="text-xs text-muted-foreground">
                                  +{portfolio.media.length - 3} more files
                                </p>
                              )}
                            </div>
                          </div>
                        )}
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <FileText className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                    <p className="text-gray-600 dark:text-gray-400">No portfolio items found for this client</p>
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
                  <span className="text-sm font-medium">Portfolio Items</span>
                  <span className="text-sm text-muted-foreground">{portfolios.length}</span>
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
