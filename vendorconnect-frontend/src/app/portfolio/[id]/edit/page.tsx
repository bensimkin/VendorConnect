'use client';

import { useState, useEffect } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { toast } from 'react-hot-toast';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { 
  ArrowLeft, 
  Save, 
  Upload, 
  X, 
  Download, 
  Eye, 
  Trash2,
  FileText,
  Image,
  Presentation,
  Archive
} from 'lucide-react';
import { apiClient } from '@/lib/api-client';

interface Client {
  id: number;
  name: string;
  company?: string;
}

interface Project {
  id: number;
  title: string;
}

interface Task {
  id: number;
  title: string;
  project_id: number;
}

interface TaskType {
  id: number;
  task_type: string;
}

interface Portfolio {
  id: number;
  title: string;
  description?: string;
  deliverable_type: 'design' | 'document' | 'presentation' | 'other';
  status: 'completed' | 'in_progress' | 'review';
  client_id: number;
  task_id?: number;
  project_id?: number;
  task_type_id?: number;
  media: Array<{
    id: number;
    file_name: string;
    mime_type: string;
    size: number;
    original_url: string;
    created_at: string;
  }>;
}

export default function EditPortfolioPage() {
  const router = useRouter();
  const params = useParams();
  const portfolioId = params.id as string;

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [portfolio, setPortfolio] = useState<Portfolio | null>(null);
  const [clients, setClients] = useState<Client[]>([]);
  const [projects, setProjects] = useState<Project[]>([]);
  const [allTasks, setAllTasks] = useState<Task[]>([]);
  const [filteredTasks, setFilteredTasks] = useState<Task[]>([]);
  const [taskTypes, setTaskTypes] = useState<TaskType[]>([]);
  const [selectedFiles, setSelectedFiles] = useState<File[]>([]);

  const [formData, setFormData] = useState({
    title: '',
    description: '',
    client_id: '',
    project_id: '',
    task_id: '',
    task_type_id: '',
    deliverable_type: 'design' as const,
    status: 'completed' as const,
  });

  useEffect(() => {
    if (portfolioId) {
      fetchData();
    }
  }, [portfolioId]);

  const fetchData = async () => {
    try {
      // Fetch portfolio data
      const portfolioResponse = await apiClient.get(`/portfolios/${portfolioId}`);
      const portfolioData = portfolioResponse.data.data;
      setPortfolio(portfolioData);

      // Set form data
      setFormData({
        title: portfolioData.title || '',
        description: portfolioData.description || '',
        client_id: portfolioData.client_id?.toString() || '',
        project_id: portfolioData.project_id?.toString() || '',
        task_id: portfolioData.task_id?.toString() || '',
        task_type_id: portfolioData.task_type_id?.toString() || '',
        deliverable_type: portfolioData.deliverable_type || 'design',
        status: portfolioData.status || 'completed',
      });

      // Fetch clients
      const clientsResponse = await apiClient.get('/clients?per_page=all');
      setClients(clientsResponse.data.data || []);

      // Fetch projects
      const projectsResponse = await apiClient.get('/projects?per_page=all');
      setProjects(projectsResponse.data.data || []);

      // Fetch tasks
      const tasksResponse = await apiClient.get('/tasks?per_page=all');
      setAllTasks(tasksResponse.data.data || []);
      setFilteredTasks(tasksResponse.data.data || []);

      // Fetch task types
      const taskTypesResponse = await apiClient.get('/task-types?per_page=all');
      setTaskTypes(taskTypesResponse.data.data || []);
    } catch (error) {
      console.error('Failed to fetch data:', error);
      toast.error('Failed to load portfolio data');
      router.push('/portfolio');
    } finally {
      setLoading(false);
    }
  };

  const handleFileSelect = (event: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(event.target.files || []);
    setSelectedFiles(prev => [...prev, ...files]);
  };

  const removeFile = (index: number) => {
    setSelectedFiles(prev => prev.filter((_, i) => i !== index));
  };

  const handleDeleteMedia = async (mediaId: number) => {
    if (!confirm('Are you sure you want to delete this file?')) {
      return;
    }

    try {
      await apiClient.delete(`/portfolios/${portfolioId}/media/${mediaId}`);
      toast.success('File deleted successfully');
      fetchData(); // Refresh data
    } catch (error) {
      console.error('Failed to delete file:', error);
      toast.error('Failed to delete file');
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.title.trim()) {
      toast.error('Portfolio title is required');
      return;
    }

    if (!formData.client_id) {
      toast.error('Please select a client');
      return;
    }

    setSaving(true);
    try {
      // Update portfolio item
      const portfolioPayload = {
        title: formData.title,
        description: formData.description,
        client_id: parseInt(formData.client_id),
        task_id: formData.task_id ? parseInt(formData.task_id) : null,
        project_id: formData.project_id && formData.project_id !== 'all' ? parseInt(formData.project_id) : null,
        task_type_id: formData.task_type_id ? parseInt(formData.task_type_id) : null,
        deliverable_type: formData.deliverable_type,
        status: formData.status,
      };

      await apiClient.put(`/portfolios/${portfolioId}`, portfolioPayload);

      // Upload new files if any
      if (selectedFiles.length > 0) {
        const formDataFiles = new FormData();
        selectedFiles.forEach((file, index) => {
          formDataFiles.append(`files[${index}]`, file);
        });

        await apiClient.post(`/portfolios/${portfolioId}/media`, formDataFiles, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });
      }

      toast.success('Portfolio item updated successfully');
      router.push(`/portfolio/${portfolioId}`);
    } catch (error: any) {
      console.error('Failed to update portfolio item:', error);
      toast.error(error.response?.data?.message || 'Failed to update portfolio item');
    } finally {
      setSaving(false);
    }
  };

  const getFileIcon = (mimeType: string) => {
    if (mimeType.startsWith('image/')) {
      return <Image className="h-5 w-5 text-blue-500" />;
    } else if (mimeType.includes('pdf')) {
      return <FileText className="h-5 w-5 text-red-500" />;
    } else if (mimeType.includes('presentation') || mimeType.includes('powerpoint')) {
      return <Presentation className="h-5 w-5 text-orange-500" />;
    } else {
      return <Archive className="h-5 w-5 text-gray-500" />;
    }
  };

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading portfolio...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  if (!portfolio) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <p className="text-muted-foreground">Portfolio not found</p>
            <Button onClick={() => router.push('/portfolio')} className="mt-4">
              Back to Portfolio
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
            onClick={() => router.push(`/portfolio/${portfolioId}`)}
          >
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div>
            <h1 className="text-2xl font-bold">Edit Portfolio Item</h1>
            <p className="text-muted-foreground">Update portfolio details and files</p>
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
                  <Label htmlFor="title">Title *</Label>
                  <Input
                    id="title"
                    value={formData.title}
                    onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                    placeholder="Enter portfolio title"
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="description">Description</Label>
                  <Textarea
                    id="description"
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    placeholder="Enter portfolio description"
                    rows={4}
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="deliverable_type">Deliverable Type *</Label>
                  <Select
                    value={formData.deliverable_type}
                    onValueChange={(value: any) => setFormData({ ...formData, deliverable_type: value })}
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="design">Design</SelectItem>
                      <SelectItem value="document">Document</SelectItem>
                      <SelectItem value="presentation">Presentation</SelectItem>
                      <SelectItem value="other">Other</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="status">Status *</Label>
                  <Select
                    value={formData.status}
                    onValueChange={(value: any) => setFormData({ ...formData, status: value })}
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="completed">Completed</SelectItem>
                      <SelectItem value="in_progress">In Progress</SelectItem>
                      <SelectItem value="review">Review</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </CardContent>
            </Card>

            {/* Client & Project Information */}
            <Card>
              <CardHeader>
                <CardTitle>Client & Project Information</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
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
                          {`${client.first_name || ''} ${client.last_name || ''}`.trim()} {client.company && `(${client.company})`}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="project_id">Related Project</Label>
                  <Select
                    value={formData.project_id}
                    onValueChange={(value) => setFormData({ ...formData, project_id: value, task_id: '' })}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select a project (optional)" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">All Projects</SelectItem>
                      {projects.map((project) => (
                        <SelectItem key={project.id} value={project.id.toString()}>
                          {project.title}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="task_id">Related Task</Label>
                  <Select
                    value={formData.task_id}
                    onValueChange={(value) => setFormData({ ...formData, task_id: value })}
                    disabled={!formData.project_id || formData.project_id === 'all'}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder={formData.project_id && formData.project_id !== 'all' ? "Select a task (optional)" : "Select a project first"} />
                    </SelectTrigger>
                    <SelectContent>
                      {filteredTasks.length > 0 ? (
                        filteredTasks.map((task) => (
                          <SelectItem key={task.id} value={task.id.toString()}>
                            {task.title}
                          </SelectItem>
                        ))
                      ) : (
                        <div className="px-2 py-1.5 text-sm text-muted-foreground">
                          {formData.project_id && formData.project_id !== 'all' ? "No tasks found for this project" : "No tasks available"}
                        </div>
                      )}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="task_type_id">Task Type</Label>
                  <Select
                    value={formData.task_type_id}
                    onValueChange={(value) => setFormData({ ...formData, task_type_id: value })}
                    disabled={!!formData.task_id}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder={formData.task_id ? "Auto-populated from task" : "Select task type (optional)"} />
                    </SelectTrigger>
                    <SelectContent>
                      {taskTypes.map((type) => (
                        <SelectItem key={type.id} value={type.id.toString()}>
                          {type.task_type}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  {formData.task_id && (
                    <p className="text-xs text-muted-foreground">
                      Task type is automatically set based on the selected task
                    </p>
                  )}
                </div>
              </CardContent>
            </Card>
          </div>

          {/* File Upload */}
          <Card>
            <CardHeader>
              <CardTitle>Files & Deliverables</CardTitle>
              <CardDescription>Upload new files or manage existing ones</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              {/* Existing Files */}
              {portfolio.media.length > 0 && (
                <div className="space-y-3">
                  <Label>Existing Files ({portfolio.media.length})</Label>
                  <div className="space-y-2">
                    {portfolio.media.map((file) => (
                      <div
                        key={file.id}
                        className="flex items-center justify-between p-3 border rounded-lg"
                      >
                        <div className="flex items-center gap-3">
                          {getFileIcon(file.mime_type)}
                          <div>
                            <p className="font-medium">{file.file_name}</p>
                            <p className="text-sm text-muted-foreground">
                              {formatFileSize(file.size)} • {formatDate(file.created_at)}
                            </p>
                          </div>
                        </div>
                        <div className="flex gap-2">
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => window.open(file.original_url, '_blank')}
                          >
                            <Eye className="h-4 w-4 mr-1" />
                            View
                          </Button>
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => {
                              const link = document.createElement('a');
                              link.href = file.original_url;
                              link.download = file.file_name;
                              link.click();
                            }}
                          >
                            <Download className="h-4 w-4 mr-1" />
                            Download
                          </Button>
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handleDeleteMedia(file.id)}
                          >
                            <Trash2 className="h-4 w-4 mr-1" />
                            Delete
                          </Button>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* Upload New Files */}
              <div className="space-y-3">
                <Label>Upload New Files</Label>
                <div className="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                  <Upload className="mx-auto h-12 w-12 text-gray-400" />
                  <div className="mt-4">
                    <input
                      type="file"
                      id="files"
                      multiple
                      onChange={handleFileSelect}
                      className="hidden"
                      accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                    />
                    <label htmlFor="files" className="cursor-pointer">
                      <Button variant="outline" type="button">
                        Choose Files
                      </Button>
                    </label>
                    <p className="text-sm text-gray-500 mt-2">
                      Upload images, documents, presentations, or other files
                    </p>
                  </div>
                </div>
              </div>

              {/* Selected Files List */}
              {selectedFiles.length > 0 && (
                <div className="space-y-2">
                  <Label>New Files to Upload ({selectedFiles.length})</Label>
                  <div className="space-y-2">
                    {selectedFiles.map((file, index) => (
                      <div key={index} className="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <div className="flex items-center gap-2">
                          <FileText className="h-4 w-4 text-gray-500" />
                          <span className="text-sm">{file.name}</span>
                          <span className="text-xs text-gray-500">
                            ({(file.size / 1024 / 1024).toFixed(2)} MB)
                          </span>
                        </div>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => removeFile(index)}
                        >
                          <X className="h-4 w-4" />
                        </Button>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Submit Button */}
          <div className="flex justify-end gap-4">
            <Button
              type="button"
              variant="outline"
              onClick={() => router.push(`/portfolio/${portfolioId}`)}
            >
              Cancel
            </Button>
            <Button type="submit" disabled={saving}>
              <Save className="h-4 w-4 mr-2" />
              {saving ? 'Saving...' : 'Save Changes'}
            </Button>
          </div>
        </form>
      </div>
    </MainLayout>
  );
}
