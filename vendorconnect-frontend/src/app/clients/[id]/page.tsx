'use client';

import { useState, useEffect } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { toast } from 'react-hot-toast';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, Trash2, Building, Mail, Phone, MapPin, Globe, Calendar, Briefcase, Users, Plus } from 'lucide-react';
import { apiClient } from '@/lib/api-client';
import { format } from 'date-fns';

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
}

interface Project {
  id: number;
  name: string;
  description?: string;
  status?: string;
  start_date?: string;
  end_date?: string;
  created_at: string;
}

interface Task {
  id: number;
  title: string;
  description?: string;
  status?: {
    name: string;
    color?: string;
  };
  priority?: {
    name: string;
    color?: string;
  };
  due_date?: string;
  created_at: string;
}

export default function ClientDetailPage() {
  const router = useRouter();
  const params = useParams();
  const clientId = params.id as string;

  const [client, setClient] = useState<Client | null>(null);
  const [projects, setProjects] = useState<Project[]>([]);
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchClientData();
  }, [clientId]);

  const fetchClientData = async () => {
    try {
      const [clientRes, projectsRes, tasksRes] = await Promise.all([
        apiClient.get(`/clients/${clientId}`),
        apiClient.get(`/clients/${clientId}/projects`),
        apiClient.get(`/clients/${clientId}/tasks`),
      ]);

      setClient(clientRes.data.data);
      setProjects(projectsRes.data.data?.data || projectsRes.data.data || []);
      setTasks(tasksRes.data.data?.data || tasksRes.data.data || []);
    } catch (error: any) {
      console.error('Failed to fetch client data:', error);
      toast.error('Failed to load client data');
      router.push('/clients');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async () => {
    if (!client || !confirm('Are you sure you want to delete this client? This action cannot be undone.')) return;

    try {
      await apiClient.delete(`/clients/${client.id}`);
      toast.success('Client deleted successfully');
      router.push('/clients');
    } catch (error: any) {
      console.error('Failed to delete client:', error);
      toast.error('Failed to delete client');
    }
  };

  const formatDate = (dateString?: string) => {
    if (!dateString) return 'Not set';
    try {
      return format(new Date(dateString), 'PPP');
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
            <p className="mt-2 text-muted-foreground">Loading client...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  if (!client) {
    return (
      <MainLayout>
        <div className="text-center py-12">
          <p className="text-muted-foreground">Client not found</p>
          <Button onClick={() => router.push('/clients')} className="mt-4">
            Back to Clients
          </Button>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Button
              variant="ghost"
              size="icon"
              onClick={() => router.push('/clients')}
            >
              <ArrowLeft className="h-4 w-4" />
            </Button>
            <div>
              <h1 className="text-2xl font-bold">{client.name}</h1>
              {client.company && (
                <p className="text-sm text-muted-foreground">{client.company}</p>
              )}
            </div>
          </div>
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              onClick={() => router.push(`/clients/${client.id}/edit`)}
            >
              <Edit className="h-4 w-4 mr-2" />
              Edit
            </Button>
            <Button
              variant="destructive"
              onClick={handleDelete}
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
                  {client.email && (
                    <div className="flex items-center space-x-2">
                      <Mail className="h-4 w-4 text-muted-foreground" />
                      <div>
                        <p className="text-sm font-medium">Email</p>
                        <p className="text-sm text-muted-foreground">{client.email}</p>
                      </div>
                    </div>
                  )}
                  {client.phone && (
                    <div className="flex items-center space-x-2">
                      <Phone className="h-4 w-4 text-muted-foreground" />
                      <div>
                        <p className="text-sm font-medium">Phone</p>
                        <p className="text-sm text-muted-foreground">{client.phone}</p>
                      </div>
                    </div>
                  )}
                  {client.website && (
                    <div className="flex items-center space-x-2">
                      <Globe className="h-4 w-4 text-muted-foreground" />
                      <div>
                        <p className="text-sm font-medium">Website</p>
                        <a 
                          href={client.website.startsWith('http') ? client.website : `https://${client.website}`}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="text-sm text-blue-600 hover:underline"
                        >
                          {client.website}
                        </a>
                      </div>
                    </div>
                  )}
                  {client.address && (
                    <div className="flex items-start space-x-2">
                      <MapPin className="h-4 w-4 text-muted-foreground mt-0.5" />
                      <div>
                        <p className="text-sm font-medium">Address</p>
                        <p className="text-sm text-muted-foreground whitespace-pre-wrap">{client.address}</p>
                      </div>
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>

            {/* Notes */}
            {client.notes && (
              <Card>
                <CardHeader>
                  <CardTitle>Notes</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-sm text-muted-foreground whitespace-pre-wrap">
                    {client.notes}
                  </p>
                </CardContent>
              </Card>
            )}

            {/* Projects */}
            <Card>
              <CardHeader>
                <CardTitle>Projects ({projects.length})</CardTitle>
              </CardHeader>
              <CardContent>
                {projects.length > 0 ? (
                  <div className="space-y-4">
                    {projects.map((project) => (
                      <div key={project.id} className="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                          <h3 className="font-medium">{project.name}</h3>
                          {project.description && (
                            <p className="text-sm text-muted-foreground mt-1">
                              {project.description}
                            </p>
                          )}
                          <div className="flex items-center space-x-4 mt-2 text-xs text-muted-foreground">
                            <span>Created {formatDate(project.created_at)}</span>
                            {project.start_date && (
                              <span>Started {formatDate(project.start_date)}</span>
                            )}
                          </div>
                        </div>
                        <div className="flex items-center space-x-2">
                          {project.status && (
                            <Badge variant="outline">{project.status}</Badge>
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
                  <p className="text-sm text-muted-foreground text-center py-4">
                    No projects found for this client
                  </p>
                )}
              </CardContent>
            </Card>

            {/* Tasks */}
            <Card>
              <CardHeader>
                <CardTitle>Recent Tasks ({tasks.length})</CardTitle>
              </CardHeader>
              <CardContent>
                {tasks.length > 0 ? (
                  <div className="space-y-4">
                    {tasks.slice(0, 5).map((task) => (
                      <div key={task.id} className="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                          <h3 className="font-medium">{task.title}</h3>
                          {task.description && (
                            <p className="text-sm text-muted-foreground mt-1">
                              {task.description}
                            </p>
                          )}
                          <div className="flex items-center space-x-4 mt-2 text-xs text-muted-foreground">
                            <span>Created {formatDate(task.created_at)}</span>
                            {task.due_date && (
                              <span>Due {formatDate(task.due_date)}</span>
                            )}
                          </div>
                        </div>
                        <div className="flex items-center space-x-2">
                          {task.status && (
                            <Badge
                              style={{
                                backgroundColor: task.status.color ? `${task.status.color}20` : undefined,
                                color: task.status.color || undefined,
                              }}
                            >
                              {task.status.name}
                            </Badge>
                          )}
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => router.push(`/tasks/${task.id}`)}
                          >
                            View
                          </Button>
                        </div>
                      </div>
                    ))}
                    {tasks.length > 5 && (
                      <div className="text-center pt-4">
                        <Button variant="outline" size="sm">
                          View All Tasks
                        </Button>
                      </div>
                    )}
                  </div>
                ) : (
                  <p className="text-sm text-muted-foreground text-center py-4">
                    No tasks found for this client
                  </p>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Client Details */}
            <Card>
              <CardHeader>
                <CardTitle>Client Details</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <p className="text-sm font-medium mb-2">Status</p>
                  <Badge
                    variant={client.status === 1 ? "default" : "secondary"}
                  >
                    {client.status === 1 ? 'Active' : 'Inactive'}
                  </Badge>
                </div>

                <div>
                  <p className="text-sm font-medium mb-2">Member Since</p>
                  <p className="text-sm text-muted-foreground">
                    {formatDate(client.created_at)}
                  </p>
                </div>

                <div>
                  <p className="text-sm font-medium mb-2">Last Updated</p>
                  <p className="text-sm text-muted-foreground">
                    {formatDate(client.updated_at)}
                  </p>
                </div>
              </CardContent>
            </Card>

            {/* Quick Actions */}
            <Card>
              <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <Button
                  variant="outline"
                  className="w-full justify-start"
                  onClick={() => router.push(`/projects/new?client_id=${client.id}`)}
                >
                  <Plus className="mr-2 h-4 w-4" />
                  New Project
                </Button>
                <Button
                  variant="outline"
                  className="w-full justify-start"
                  onClick={() => router.push(`/tasks/new?client_id=${client.id}`)}
                >
                  <Plus className="mr-2 h-4 w-4" />
                  New Task
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </MainLayout>
  );
}
