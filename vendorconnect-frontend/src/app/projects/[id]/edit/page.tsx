'use client';

import { useState, useEffect } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { toast } from 'react-hot-toast';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft } from 'lucide-react';
import { apiClient } from '@/lib/api-client';

interface Project {
  id: number;
  title: string;
  description?: string;
  start_date: string;
  end_date?: string;
  status_id: number;
  budget?: number;
  client_id?: number;
  clients?: Array<{ id: number; name: string; company?: string }>;
  status?: { id: number; title: string };
}

interface Client {
  id: number;
  name: string;
  company?: string;
}

interface Status {
  id: number;
  title: string;
}

export default function EditProjectPage() {
  const router = useRouter();
  const params = useParams();
  const projectId = params.id as string;

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [project, setProject] = useState<Project | null>(null);
  const [clients, setClients] = useState<Client[]>([]);
  const [statuses, setStatuses] = useState<Status[]>([]);

  const [formData, setFormData] = useState({
    title: '',
    description: '',
    client_id: '',
    start_date: '',
    end_date: '',
    budget: '',
    status_id: '',
  });

  useEffect(() => {
    if (projectId) {
      fetchProject();
      fetchFormData();
    }
  }, [projectId]);

  const fetchProject = async () => {
    try {
      const response = await apiClient.get(`/projects/${projectId}`);
      const projectData = response.data.data;
      setProject(projectData);
      
      // Pre-populate form data
      setFormData({
        title: projectData.title || '',
        description: projectData.description || '',
        client_id: projectData.clients?.[0]?.id?.toString() || '',
        start_date: projectData.start_date || '',
        end_date: projectData.end_date || '',
        budget: projectData.budget?.toString() || '',
        status_id: projectData.status_id?.toString() || '',
      });
    } catch (error: any) {
      console.error('Failed to fetch project:', error);
      toast.error('Failed to load project details');
      router.push('/projects');
    } finally {
      setLoading(false);
    }
  };

  const fetchFormData = async () => {
    try {
      // Fetch clients
      const clientsResponse = await apiClient.get('/clients');
      setClients(clientsResponse.data.data || []);

      // Fetch project statuses (Active, Inactive, Completed)
      const statusesResponse = await apiClient.get('/statuses');
      const allStatuses = statusesResponse.data.data || [];
      const projectStatuses = allStatuses.filter((status: any) => 
        ['active', 'inactive', 'completed'].includes(status.slug)
      );
      setStatuses(projectStatuses);
    } catch (error) {
      console.error('Failed to fetch form data:', error);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.title.trim()) {
      toast.error('Project title is required');
      return;
    }

    if (!formData.client_id) {
      toast.error('Please select a client');
      return;
    }

    setSaving(true);
    try {
      const payload = {
        title: formData.title,
        description: formData.description,
        client_id: parseInt(formData.client_id),
        start_date: formData.start_date,
        end_date: formData.end_date || null,
        budget: formData.budget ? parseFloat(formData.budget) : null,
        status_id: parseInt(formData.status_id) || 1,
      };

      await apiClient.put(`/projects/${projectId}`, payload);
      toast.success('Project updated successfully');
      router.push(`/projects/${projectId}`);
    } catch (error: any) {
      console.error('Failed to update project:', error);
      toast.error(error.response?.data?.message || 'Failed to update project');
    } finally {
      setSaving(false);
    }
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
            onClick={() => router.push(`/projects/${projectId}`)}
          >
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div>
            <h1 className="text-2xl font-bold">Edit Project</h1>
            <p className="text-muted-foreground">Update project information</p>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid gap-6 md:grid-cols-2">
            {/* Basic Information */}
            <Card>
              <CardHeader>
                <CardTitle>Basic Information</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="title">Project Name *</Label>
                  <Input
                    id="title"
                    value={formData.title}
                    onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                    placeholder="Enter project name"
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="description">Description</Label>
                  <Textarea
                    id="description"
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    placeholder="Enter project description"
                    rows={4}
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="client_id">Client *</Label>
                  <Select
                    value={formData.client_id}
                    onValueChange={(value) => setFormData({ ...formData, client_id: value })}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select a client" />
                    </SelectTrigger>
                    <SelectContent>
                      {clients.map((client) => (
                        <SelectItem key={client.id} value={client.id.toString()}>
                          {client.name} {client.company && `(${client.company})`}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="status_id">Status</Label>
                  <Select
                    value={formData.status_id}
                    onValueChange={(value) => setFormData({ ...formData, status_id: value })}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                      {statuses.map((status) => (
                        <SelectItem key={status.id} value={status.id.toString()}>
                          {status.title}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </CardContent>
            </Card>

            {/* Timeline & Budget */}
            <Card>
              <CardHeader>
                <CardTitle>Timeline & Budget</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="start_date">Start Date *</Label>
                  <Input
                    id="start_date"
                    type="date"
                    value={formData.start_date}
                    onChange={(e) => setFormData({ ...formData, start_date: e.target.value })}
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="end_date">End Date (Optional)</Label>
                  <Input
                    id="end_date"
                    type="date"
                    value={formData.end_date}
                    onChange={(e) => setFormData({ ...formData, end_date: e.target.value })}
                    min={formData.start_date}
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="budget">Budget</Label>
                  <Input
                    id="budget"
                    type="number"
                    value={formData.budget}
                    onChange={(e) => setFormData({ ...formData, budget: e.target.value })}
                    placeholder="Enter budget amount"
                    min="0"
                    step="0.01"
                  />
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Action Buttons */}
          <div className="flex items-center gap-4">
            <Button type="submit" disabled={saving}>
              {saving ? 'Saving...' : 'Save Changes'}
            </Button>
            <Button
              type="button"
              variant="outline"
              onClick={() => router.push(`/projects/${projectId}`)}
            >
              Cancel
            </Button>
          </div>
        </form>
      </div>
    </MainLayout>
  );
}
