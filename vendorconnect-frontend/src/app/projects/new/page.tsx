'use client';

import { useState, useEffect, Suspense } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { toast } from 'react-hot-toast';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { MultiSelect } from '@/components/ui/multi-select';
import { ArrowLeft, Save } from 'lucide-react';
import { apiClient } from '@/lib/api-client';
import { useProjectSettings } from '@/hooks/use-project-settings';

interface Client {
  id: number;
  name: string;
  first_name?: string;
  last_name?: string;
  company?: string;
}

interface Status {
  id: number;
  title: string;
  color?: string;
}

function NewProjectPageContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const clientId = searchParams.get('client_id');
  const { settings, loading: settingsLoading } = useProjectSettings();

  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [clients, setClients] = useState<Client[]>([]);
  const [statuses, setStatuses] = useState<Status[]>([]);
  

  
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    client_id: clientId || '',
    client_ids: clientId ? [parseInt(clientId)] : [],
    status_id: '',
    start_date: new Date().toISOString().split('T')[0], // Default to today
    end_date: '',
    budget: '',
  });

  useEffect(() => {
    fetchClients();
    fetchStatuses();
  }, []);

  const fetchClients = async () => {
    try {
      const response = await apiClient.get('/clients');
      const clientData = response.data.data?.data || response.data.data || [];
      setClients(clientData);
    } catch (error) {
      console.error('Failed to fetch clients:', error);
      toast.error('Failed to load clients');
    }
  };

  const fetchStatuses = async () => {
    try {
      const response = await apiClient.get('/statuses');
      const statusData = response.data.data || [];
      setStatuses(statusData);
    } catch (error) {
      console.error('Failed to fetch statuses:', error);
      toast.error('Failed to load statuses');
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.title.trim()) {
      toast.error('Project name is required');
      return;
    }

    if (settings.require_project_client) {
      if (settings.allow_multiple_clients_per_project) {
        if (formData.client_ids.length === 0) {
          toast.error('Please select at least one client');
          return;
        }
      } else {
        if (!formData.client_id) {
          toast.error('Please select a client');
          return;
        }
      }
    }

    setSaving(true);
    try {
      const payload = {
        ...formData,
        client_id: settings.allow_multiple_clients_per_project ? null : parseInt(formData.client_id),
        client_ids: settings.allow_multiple_clients_per_project ? formData.client_ids : [],
        status_id: formData.status_id ? parseInt(formData.status_id) : null,
        budget: formData.budget ? parseFloat(formData.budget) : null,
        end_date: formData.end_date || null, // Set to null if empty
      };

      await apiClient.post('/projects', payload);
      toast.success('Project created successfully');
      router.push('/projects');
    } catch (error: any) {
      console.error('Failed to create project:', error);
      toast.error(error.response?.data?.message || 'Failed to create project');
    } finally {
      setSaving(false);
    }
  };

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
          <div>
            <h1 className="text-2xl font-bold">New Project</h1>
            <p className="text-muted-foreground">Create a new project</p>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Project Information</CardTitle>
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
                <Label htmlFor="status_id">Status</Label>
                <Select
                  value={formData.status_id}
                  onValueChange={(value) => setFormData({ ...formData, status_id: value })}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select a status" />
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

              <div className="space-y-2">
                <Label htmlFor="client_id">
                  Client{settings.allow_multiple_clients_per_project ? 's' : ''} *
                  {settings.allow_multiple_clients_per_project && (
                    <span className="text-sm text-muted-foreground ml-2">
                      (Max: {settings.max_clients_per_project})
                    </span>
                  )}
                </Label>

                {settings.allow_multiple_clients_per_project ? (
                  <MultiSelect
                    options={clients}
                    selected={formData.client_ids}
                    onSelectionChange={(selected) => setFormData({ ...formData, client_ids: selected })}
                    placeholder="Select clients..."
                    maxSelections={settings.max_clients_per_project}
                  />
                ) : (
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
                          {`${client.first_name || ''} ${client.last_name || ''}`.trim()} {client.company && `(${client.company})`}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                )}
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="start_date">Start Date</Label>
                  <Input
                    id="start_date"
                    type="date"
                    value={formData.start_date}
                    onChange={(e) => setFormData({ ...formData, start_date: e.target.value })}
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="end_date">End Date (Optional)</Label>
                  <Input
                    id="end_date"
                    type="date"
                    value={formData.end_date}
                    onChange={(e) => setFormData({ ...formData, end_date: e.target.value })}
                    min={formData.start_date} // End date should be after start date
                  />
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="budget">Budget</Label>
                <Input
                  id="budget"
                  type="number"
                  step="0.01"
                  value={formData.budget}
                  onChange={(e) => setFormData({ ...formData, budget: e.target.value })}
                  placeholder="Enter budget amount"
                />
              </div>
            </CardContent>
          </Card>

          <div className="flex justify-end space-x-4">
            <Button
              type="button"
              variant="outline"
              onClick={() => router.push('/projects')}
            >
              Cancel
            </Button>
            <Button type="submit" disabled={saving}>
              {saving ? 'Creating...' : 'Create Project'}
            </Button>
          </div>
        </form>
      </div>
    </MainLayout>
  );
}

export default function NewProjectPage() {
  return (
    <Suspense fallback={<div>Loading...</div>}>
      <NewProjectPageContent />
    </Suspense>
  );
}
