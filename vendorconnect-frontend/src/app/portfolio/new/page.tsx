'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { toast } from 'react-hot-toast';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft, Upload, X } from 'lucide-react';
import { apiClient } from '@/lib/api-client';

interface Client {
  id: number;
  name: string;
  company?: string;
}

interface Task {
  id: number;
  title: string;
  project_id?: number;
  task_type_id?: number;
}

interface Project {
  id: number;
  title: string;
}

interface TaskType {
  id: number;
  task_type: string;
}

export default function NewPortfolioPage() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [clients, setClients] = useState<Client[]>([]);
  const [allTasks, setAllTasks] = useState<Task[]>([]);
  const [filteredTasks, setFilteredTasks] = useState<Task[]>([]);
  const [projects, setProjects] = useState<Project[]>([]);
  const [taskTypes, setTaskTypes] = useState<TaskType[]>([]);
  const [selectedFiles, setSelectedFiles] = useState<File[]>([]);

  const [formData, setFormData] = useState({
    title: '',
    description: '',
    client_id: '',
    task_id: '',
    project_id: '',
    task_type_id: '',
    deliverable_type: 'design',
    status: 'completed',
  });

  useEffect(() => {
    fetchFormData();
  }, []);

  // Filter tasks when project changes
  useEffect(() => {
    if (formData.project_id) {
      const projectTasks = allTasks.filter(task => task.project_id === parseInt(formData.project_id));
      setFilteredTasks(projectTasks);
      
      // Clear task and task type if they don't belong to the selected project
      if (formData.task_id) {
        const selectedTask = projectTasks.find(task => task.id === parseInt(formData.task_id));
        if (!selectedTask) {
          setFormData(prev => ({ ...prev, task_id: '', task_type_id: '' }));
        }
      }
    } else {
      setFilteredTasks(allTasks);
    }
  }, [formData.project_id, allTasks]);

  // Auto-populate task type when task is selected
  useEffect(() => {
    if (formData.task_id) {
      const selectedTask = allTasks.find(task => task.id === parseInt(formData.task_id));
      if (selectedTask?.task_type_id !== undefined) {
        setFormData(prev => ({ ...prev, task_type_id: selectedTask.task_type_id.toString() }));
      }
    }
  }, [formData.task_id, allTasks]);

  const fetchFormData = async () => {
    try {
      // Fetch clients
      const clientsResponse = await apiClient.get('/clients');
      setClients(clientsResponse.data.data || []);

      // Fetch tasks with project and task type info
      const tasksResponse = await apiClient.get('/tasks');
      setAllTasks(tasksResponse.data.data || []);
      setFilteredTasks(tasksResponse.data.data || []);

      // Fetch projects
      const projectsResponse = await apiClient.get('/projects');
      setProjects(projectsResponse.data.data || []);

      // Fetch task types
      const taskTypesResponse = await apiClient.get('/task-types?per_page=all');
      setTaskTypes(taskTypesResponse.data.data || []);
    } catch (error) {
      console.error('Failed to fetch form data:', error);
      toast.error('Failed to load form data');
    }
  };

  const handleFileSelect = (event: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(event.target.files || []);
    setSelectedFiles(prev => [...prev, ...files]);
  };

  const removeFile = (index: number) => {
    setSelectedFiles(prev => prev.filter((_, i) => i !== index));
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
      // Create portfolio item
      const portfolioPayload = {
        title: formData.title,
        description: formData.description,
        client_id: parseInt(formData.client_id),
        task_id: formData.task_id ? parseInt(formData.task_id) : null,
        project_id: formData.project_id ? parseInt(formData.project_id) : null,
        task_type_id: formData.task_type_id ? parseInt(formData.task_type_id) : null,
        deliverable_type: formData.deliverable_type,
        status: formData.status,
      };

      const portfolioResponse = await apiClient.post('/portfolios', portfolioPayload);
      const portfolioId = portfolioResponse.data.data.id;

      // Upload files if any
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

      toast.success('Portfolio item created successfully');
      router.push(`/portfolio/${portfolioId}`);
    } catch (error: any) {
      console.error('Failed to create portfolio item:', error);
      toast.error(error.response?.data?.message || 'Failed to create portfolio item');
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
            onClick={() => router.push('/portfolio')}
          >
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div>
            <h1 className="text-2xl font-bold">Add Portfolio Item</h1>
            <p className="text-muted-foreground">Create a new portfolio item with deliverables</p>
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
                    placeholder="Enter portfolio item title"
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="description">Description</Label>
                  <Textarea
                    id="description"
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    placeholder="Enter description"
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
                  <Label htmlFor="deliverable_type">Deliverable Type</Label>
                  <Select
                    value={formData.deliverable_type}
                    onValueChange={(value) => setFormData({ ...formData, deliverable_type: value })}
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
                  <Label htmlFor="status">Status</Label>
                  <Select
                    value={formData.status}
                    onValueChange={(value) => setFormData({ ...formData, status: value })}
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

            {/* Related Items */}
            <Card>
              <CardHeader>
                <CardTitle>Related Items (Optional)</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="project_id">Related Project</Label>
                  <Select
                    value={formData.project_id}
                    onValueChange={(value) => setFormData({ ...formData, project_id: value, task_id: '', task_type_id: '' })}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select a project (optional)" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="">All Projects</SelectItem>
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
                    disabled={!formData.project_id}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder={formData.project_id ? "Select a task (optional)" : "Select a project first"} />
                    </SelectTrigger>
                    <SelectContent>
                      {filteredTasks.length > 0 ? (
                        filteredTasks.map((task) => (
                          <SelectItem key={task.id} value={task.id.toString()}>
                            {task.title}
                          </SelectItem>
                        ))
                      ) : (
                        <SelectItem value="" disabled>
                          {formData.project_id ? "No tasks found for this project" : "No tasks available"}
                        </SelectItem>
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
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="files">Upload Files</Label>
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

              {selectedFiles.length > 0 && (
                <div className="space-y-2">
                  <Label>Selected Files</Label>
                  <div className="space-y-2">
                    {selectedFiles.map((file, index) => (
                      <div key={index} className="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <span className="text-sm">{file.name}</span>
                        <Button
                          type="button"
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

          {/* Action Buttons */}
          <div className="flex items-center gap-4">
            <Button type="submit" disabled={saving}>
              {saving ? 'Creating...' : 'Create Portfolio Item'}
            </Button>
            <Button
              type="button"
              variant="outline"
              onClick={() => router.push('/portfolio')}
            >
              Cancel
            </Button>
          </div>
        </form>
      </div>
    </MainLayout>
  );
}
