'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import apiClient from '@/lib/api-client';
import { toast } from 'react-hot-toast';
import { ArrowLeft, Save, Calendar, FileText, HelpCircle, CheckSquare, Upload, Link, X, Paperclip } from 'lucide-react';
import { format } from 'date-fns';

interface Status {
  id: number;
  name: string;
}

interface Priority {
  id: number;
  name: string;
}

interface User {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
}

interface Project {
  id: number;
  title: string;
}

interface Client {
  id: number;
  name: string;
  company?: string;
}

interface Template {
  id: number;
  title: string;
  task_type_id: number;
  standard_brief?: string;
  description?: string;
  deliverable_quantity?: number;
}

interface TaskType {
  id: number;
  name: string;
}

interface TemplateQuestion {
  id: number;
  question_text: string;
  question_type: 'text' | 'textarea' | 'select' | 'checkbox' | 'radio';
  options?: string[];
}

interface TemplateChecklist {
  id: number;
  checklist: string | string[];
}

export default function NewTaskPage() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [statuses, setStatuses] = useState<Status[]>([]);
  const [priorities, setPriorities] = useState<Priority[]>([]);
  const [users, setUsers] = useState<User[]>([]);
  const [projects, setProjects] = useState<Project[]>([]);
  const [clients, setClients] = useState<Client[]>([]);
  const [allClients, setAllClients] = useState<Client[]>([]);
  const [taskTypes, setTaskTypes] = useState<TaskType[]>([]);
  const [templates, setTemplates] = useState<Template[]>([]);
  const [selectedTemplate, setSelectedTemplate] = useState<string>('');
  const [templateQuestions, setTemplateQuestions] = useState<TemplateQuestion[]>([]);
  const [templateChecklist, setTemplateChecklist] = useState<string[]>([]);
  const [questionAnswers, setQuestionAnswers] = useState<Record<number, string>>({});
  const [uploadedFiles, setUploadedFiles] = useState<File[]>([]);
  const [googleLinks, setGoogleLinks] = useState<string[]>(['']);
  const [showLinkInput, setShowLinkInput] = useState(false);
  
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    note: '',
    status_id: '',
    priority_id: '',
    task_type_id: '',
    project_id: '',
    client_ids: [] as number[],
    user_ids: [] as number[],
    start_date: '',
    end_date: '',
    close_deadline: false,
    deliverable_quantity: 1,
    is_repeating: false,
    repeat_frequency: '',
    repeat_interval: 1,
    repeat_until: '',
  });

  useEffect(() => {
    fetchFormData();
  }, []);

  const fetchFormData = async () => {
    try {
      const [statusRes, priorityRes, userRes, projectRes, clientRes, taskTypeRes, templateRes] = await Promise.all([
        apiClient.get('/statuses'),
        apiClient.get('/priorities'),
        apiClient.get('/users'),
        apiClient.get('/projects'),
        apiClient.get('/clients'),
        apiClient.get('/task-types?per_page=all'),
        apiClient.get('/task-brief-templates'),
      ]);

      setStatuses(statusRes.data.data || []);
      setPriorities(priorityRes.data.data || []);
      setUsers(userRes.data.data?.data || userRes.data.data || []);
      setProjects(projectRes.data.data?.data || projectRes.data.data || []);
      setAllClients(clientRes.data.data?.data || clientRes.data.data || []);
      setClients(clientRes.data.data?.data || clientRes.data.data || []); // Initially show all clients
      setTaskTypes(taskTypeRes.data.data || []);
      setTemplates(templateRes.data.data?.data || templateRes.data.data || []);

      // Set default values
      const pendingStatus = statusRes.data.data?.find((s: Status) => s.name === 'Pending');
      const mediumPriority = priorityRes.data.data?.find((p: Priority) => p.name === 'Medium');
      
      if (pendingStatus) {
        setFormData(prev => ({ ...prev, status_id: pendingStatus.id.toString() }));
      }
      if (mediumPriority) {
        setFormData(prev => ({ ...prev, priority_id: mediumPriority.id.toString() }));
      }
      
      // Set start date to today and end date to 7 days from today
      const today = new Date().toISOString().split('T')[0];
      const nextWeek = new Date();
      nextWeek.setDate(nextWeek.getDate() + 7);
      const nextWeekStr = nextWeek.toISOString().split('T')[0];
      
      // Set default task type to first available one if any exist
      const defaultTaskType = taskTypeRes.data.data?.length > 0 ? taskTypeRes.data.data[0].id.toString() : '';
      
      setFormData(prev => ({ 
        ...prev, 
        start_date: today, 
        end_date: nextWeekStr,
        task_type_id: defaultTaskType
      }));
    } catch (error) {
      console.error('Failed to fetch form data:', error);
      toast.error('Failed to load form data');
    }
  };

  const handleProjectChange = async (projectId: string) => {
    setFormData({ ...formData, project_id: projectId, client_ids: [] }); // Clear client selection when project changes
    
    if (projectId) {
      try {
        // Fetch project details to get associated clients
        const projectRes = await apiClient.get(`/projects/${projectId}`);
        const projectClients = projectRes.data.data.clients || [];
        
        if (projectClients.length > 0) {
          // Filter clients to only show those associated with the selected project
          setClients(projectClients);
          // Auto-select the first client if only one is associated
          if (projectClients.length === 1) {
            setFormData(prev => ({ 
              ...prev, 
              project_id: projectId, 
              client_ids: [projectClients[0].id] 
            }));
          }
        } else {
          // If no clients associated with project, show all clients
          setClients(allClients);
        }
      } catch (error) {
        console.error('Failed to fetch project details:', error);
        // Fallback to showing all clients
        setClients(allClients);
      }
    } else {
      // If no project selected, show all clients
      setClients(allClients);
    }
  };

  const handleTemplateChange = async (templateId: string) => {
    setSelectedTemplate(templateId);
    
    if (templateId) {
      try {
        const [templateRes, questionsRes, checklistRes] = await Promise.all([
          apiClient.get(`/task-brief-templates/${templateId}`),
          apiClient.get(`/task-brief-questions?template_id=${templateId}`),
          apiClient.get(`/task-brief-checklists?template_id=${templateId}`),
        ]);
        
        const template = templateRes.data.data;
        
        // Pre-fill form with template data
        if (template.title) {
          setFormData(prev => ({
            ...prev,
            title: template.title,
            task_type_id: template.task_type_id?.toString() || '',
            description: template.standard_brief || '',
            note: template.description || '',
            deliverable_quantity: template.deliverable_quantity || 1,
          }));
        }
        
        // Load questions
        const questions = questionsRes.data.data?.data || questionsRes.data.data || [];
        setTemplateQuestions(questions);
        setQuestionAnswers({});
        
        // Load checklist
        const checklistData = checklistRes.data.data?.data || checklistRes.data.data || [];
        if (checklistData.length > 0 && checklistData[0].checklist) {
          const checklist = checklistData[0].checklist;
          if (Array.isArray(checklist)) {
            setTemplateChecklist(checklist);
          } else if (typeof checklist === 'string') {
            try {
              const parsed = JSON.parse(checklist);
              setTemplateChecklist(Array.isArray(parsed) ? parsed : [checklist]);
            } catch {
              setTemplateChecklist([checklist]);
            }
          }
        } else {
          setTemplateChecklist([]);
        }
        
        toast.success('Template loaded successfully');
      } catch (error) {
        console.error('Failed to load template:', error);
        toast.error('Failed to load template details');
      }
    } else {
      // Clear template data when no template is selected
      setTemplateQuestions([]);
      setTemplateChecklist([]);
      setQuestionAnswers({});
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.title.trim()) {
      toast.error('Task title is required');
      return;
    }

    if (!formData.project_id) {
      toast.error('Please select a project');
      return;
    }

    setLoading(true);
    try {
      const payload = {
        title: formData.title,
        description: formData.description || null,
        note: formData.note || null,
        status_id: parseInt(formData.status_id),
        priority_id: parseInt(formData.priority_id),
        task_type_id: formData.task_type_id ? parseInt(formData.task_type_id) : null,
        project_id: formData.project_id ? parseInt(formData.project_id) : null,
        client_ids: formData.client_ids,
        user_ids: formData.user_ids,
        start_date: formData.start_date || null,
        end_date: formData.end_date || null,
        close_deadline: formData.close_deadline,
        deliverable_quantity: formData.deliverable_quantity,
        is_repeating: formData.is_repeating,
        repeat_frequency: formData.repeat_frequency || null,
        repeat_interval: formData.repeat_interval,
        repeat_until: formData.repeat_until || null,
        template_id: selectedTemplate ? parseInt(selectedTemplate) : null,
      };

      const response = await apiClient.post('/tasks', payload);
      const taskId = response.data.data.id;
      
      // Upload files if any
      if (uploadedFiles.length > 0) {
        try {
          const formData = new FormData();
          uploadedFiles.forEach((file) => {
            formData.append('files[]', file);
          });
          
          await apiClient.post(`/tasks/${taskId}/media`, formData, {
            headers: {
              'Content-Type': 'multipart/form-data',
            },
          });
        } catch (error) {
          console.error('Failed to upload files:', error);
          toast.error('Task created but failed to upload some files');
        }
      }

      // Add Google links as comments if any
      const validLinks = googleLinks.filter(link => link.trim() !== '');
      if (validLinks.length > 0) {
        try {
          for (const link of validLinks) {
            await apiClient.post(`/tasks/${taskId}/messages`, {
              message_text: `📎 Reference Link: ${link}`,
            });
          }
        } catch (error) {
          console.error('Failed to add links:', error);
          toast.error('Task created but failed to add some links');
        }
      }
      
      // Save template questions and checklists to task if template was used
      if (selectedTemplate) {
        try {
          // Save template questions (even without answers)
          if (templateQuestions.length > 0) {
            for (const question of templateQuestions) {
              const answer = questionAnswers[question.id] || '';
              await apiClient.post(`/tasks/${taskId}/question-answer`, {
                question_id: question.id,
                answer: answer || '', // Ensure answer is always a string
              });
            }
          }
          
          // Save template checklist items
          if (templateChecklist.length > 0) {
            for (let i = 0; i < templateChecklist.length; i++) {
              await apiClient.post(`/tasks/${taskId}/checklist-answer`, {
                checklist_id: 1, // Assuming single checklist per template
                completed: false,
                notes: templateChecklist[i],
              });
            }
          }
        } catch (error) {
          console.error('Failed to save template data:', error);
        }
      }
      
      toast.success('Task created successfully');
      router.push(`/tasks/${taskId}`);
    } catch (error: any) {
      console.error('Failed to create task:', error);
      toast.error(error.response?.data?.message || 'Failed to create task');
    } finally {
      setLoading(false);
    }
  };

  const toggleUserSelection = (userId: number) => {
    setFormData(prev => ({
      ...prev,
      user_ids: prev.user_ids.includes(userId)
        ? prev.user_ids.filter(id => id !== userId)
        : [...prev.user_ids, userId]
    }));
  };

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files) {
      const newFiles = Array.from(e.target.files);
      setUploadedFiles(prev => [...prev, ...newFiles]);
    }
  };

  const removeFile = (index: number) => {
    setUploadedFiles(prev => prev.filter((_, i) => i !== index));
  };

  const addGoogleLink = () => {
    setGoogleLinks(prev => [...prev, '']);
    setShowLinkInput(true);
  };

  const updateGoogleLink = (index: number, value: string) => {
    setGoogleLinks(prev => prev.map((link, i) => i === index ? value : link));
  };

  const removeGoogleLink = (index: number) => {
    setGoogleLinks(prev => prev.filter((_, i) => i !== index));
  };

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  return (
    <MainLayout>
      <div className="max-w-4xl mx-auto space-y-6">
        {/* Header */}
        <div className="flex items-center gap-4">
          <Button
            variant="ghost"
            size="icon"
            onClick={() => router.push('/tasks')}
          >
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div>
            <h1 className="text-2xl font-bold">Create New Task</h1>
            <p className="text-sm text-muted-foreground">Fill in the details to create a new task</p>
          </div>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Template Selection */}
          {templates.length > 0 && (
            <Card>
              <CardHeader>
                <CardTitle>Use Template (Optional)</CardTitle>
                <CardDescription>Start with a pre-defined template</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="flex items-center gap-2">
                  <FileText className="h-4 w-4 text-muted-foreground" />
                  <select
                    value={selectedTemplate}
                    onChange={(e) => handleTemplateChange(e.target.value)}
                    className="flex-1 px-3 py-2 border rounded-md bg-background"
                  >
                    <option value="">None - Start from scratch</option>
                    {templates.map((template) => (
                      <option key={template.id} value={template.id}>
                        {template.title}
                      </option>
                    ))}
                  </select>
                </div>
              </CardContent>
            </Card>
          )}

          <Card>
            <CardHeader>
              <CardTitle>Task Details</CardTitle>
              <CardDescription>Basic information about the task</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="title">Title *</Label>
                <Input
                  id="title"
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  placeholder="Enter task title"
                  required
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="description">Description</Label>
                <Textarea
                  id="description"
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  placeholder="Enter task description"
                  rows={4}
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="note">Notes</Label>
                <Textarea
                  id="note"
                  value={formData.note || ''}
                  onChange={(e) => setFormData({ ...formData, note: e.target.value })}
                  placeholder="Enter task notes, brief, or additional instructions"
                  rows={4}
                />
                <p className="text-xs text-muted-foreground">Additional instructions, brief, or notes for the task</p>
              </div>

              <div className="grid gap-4 md:grid-cols-4">
                <div className="space-y-2">
                  <Label htmlFor="status">Status</Label>
                  <select
                    id="status"
                    value={formData.status_id}
                    onChange={(e) => setFormData({ ...formData, status_id: e.target.value })}
                    className="w-full px-3 py-2 border rounded-md bg-background"
                  >
                    {statuses.map((status) => (
                      <option key={status.id} value={status.id}>
                        {status.name}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="priority">Priority</Label>
                  <select
                    id="priority"
                    value={formData.priority_id}
                    onChange={(e) => setFormData({ ...formData, priority_id: e.target.value })}
                    className="w-full px-3 py-2 border rounded-md bg-background"
                  >
                    {priorities.map((priority) => (
                      <option key={priority.id} value={priority.id}>
                        {priority.name}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="task_type">Task Type</Label>
                  <select
                    id="task_type"
                    value={formData.task_type_id}
                    onChange={(e) => setFormData({ ...formData, task_type_id: e.target.value })}
                    className="w-full px-3 py-2 border rounded-md bg-background"
                  >
                    <option value="">Select Task Type</option>
                    {taskTypes.map((type) => (
                      <option key={type.id} value={type.id}>
                        {type.name}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="deliverable_quantity">Quantity</Label>
                  <Input
                    id="deliverable_quantity"
                    type="number"
                    min="1"
                    value={formData.deliverable_quantity}
                    onChange={(e) => setFormData({ ...formData, deliverable_quantity: parseInt(e.target.value) || 1 })}
                    placeholder="e.g., 6"
                    className="w-full"
                  />
                  <p className="text-xs text-muted-foreground">Number of deliverables needed</p>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="project">Project *</Label>
                  <select
                    id="project"
                    value={formData.project_id}
                    onChange={(e) => handleProjectChange(e.target.value)}
                    className="w-full px-3 py-2 border rounded-md bg-background"
                    required
                  >
                    <option value="">Select Project</option>
                    {projects.map((project) => (
                      <option key={project.id} value={project.id}>
                        {project.title}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="client">Client</Label>
                  <select
                    id="client"
                    value={formData.client_ids[0] || ''}
                    onChange={(e) => {
                      const clientId = e.target.value ? parseInt(e.target.value) : null;
                      setFormData({ 
                        ...formData, 
                        client_ids: clientId ? [clientId] : [] 
                      });
                    }}
                    className="w-full px-3 py-2 border rounded-md bg-background"
                  >
                    <option value="">
                      {formData.project_id 
                        ? `Select Client for ${projects.find(p => p.id.toString() === formData.project_id)?.title || 'Project'} (Optional)`
                        : 'Select Client (Optional)'
                      }
                    </option>
                    {clients.map((client) => (
                      <option key={client.id} value={client.id}>
                        {client.name} {client.company && `(${client.company})`}
                      </option>
                    ))}
                  </select>
                  {formData.project_id && clients.length === 0 && (
                    <p className="text-xs text-muted-foreground">
                      No clients associated with this project
                    </p>
                  )}
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Attachments & Links */}
          <Card>
            <CardHeader>
              <CardTitle>Attachments & Reference Links</CardTitle>
              <CardDescription>Upload files and add Google links to brief the tasker</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              {/* File Upload */}
              <div className="space-y-3">
                <div className="flex items-center gap-2">
                  <Paperclip className="h-4 w-4 text-muted-foreground" />
                  <Label>Upload Files</Label>
                </div>
                <div className="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                  <input
                    type="file"
                    multiple
                    onChange={handleFileUpload}
                    className="hidden"
                    id="file-upload"
                    accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.mp4,.mov,.zip,.rar"
                  />
                  <label htmlFor="file-upload" className="cursor-pointer">
                    <Upload className="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                    <p className="text-sm text-muted-foreground">
                      Click to upload files or drag and drop
                    </p>
                    <p className="text-xs text-muted-foreground mt-1">
                      PDF, DOC, Images, Videos, Archives (Max 10MB each)
                    </p>
                  </label>
                </div>
                
                {/* Uploaded Files List */}
                {uploadedFiles.length > 0 && (
                  <div className="space-y-2">
                    <p className="text-sm font-medium">Uploaded Files:</p>
                    {uploadedFiles.map((file, index) => (
                      <div key={index} className="flex items-center justify-between p-2 bg-gray-50 rounded-md">
                        <div className="flex items-center gap-2">
                          <Paperclip className="h-4 w-4 text-muted-foreground" />
                          <span className="text-sm">{file.name}</span>
                          <span className="text-xs text-muted-foreground">
                            ({formatFileSize(file.size)})
                          </span>
                        </div>
                        <Button
                          type="button"
                          variant="ghost"
                          size="sm"
                          onClick={() => removeFile(index)}
                          className="text-red-600 hover:text-red-700"
                        >
                          <X className="h-4 w-4" />
                        </Button>
                      </div>
                    ))}
                  </div>
                )}
              </div>

              {/* Google Links */}
              <div className="space-y-3">
                <div className="flex items-center gap-2">
                  <Link className="h-4 w-4 text-muted-foreground" />
                  <Label>Reference Links</Label>
                </div>
                <div className="space-y-2">
                  {googleLinks.map((link, index) => (
                    <div key={index} className="flex items-center gap-2">
                      <Input
                        type="url"
                        value={link}
                        onChange={(e) => updateGoogleLink(index, e.target.value)}
                        placeholder="https://docs.google.com/... or any reference link"
                        className="flex-1"
                      />
                      {googleLinks.length > 1 && (
                        <Button
                          type="button"
                          variant="ghost"
                          size="sm"
                          onClick={() => removeGoogleLink(index)}
                          className="text-red-600 hover:text-red-700"
                        >
                          <X className="h-4 w-4" />
                        </Button>
                      )}
                    </div>
                  ))}
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={addGoogleLink}
                    className="w-full"
                  >
                    <Link className="h-4 w-4 mr-2" />
                    Add Another Link
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Timeline</CardTitle>
              <CardDescription>Set start and end dates for the task</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                  <Label htmlFor="start_date">Start Date</Label>
                  <div className="relative">
                    <Calendar className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                      id="start_date"
                      type="date"
                      value={formData.start_date}
                      onChange={(e) => setFormData({ ...formData, start_date: e.target.value })}
                      className="pl-10"
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="end_date">End Date</Label>
                  <div className="relative">
                    <Calendar className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                      id="end_date"
                      type="date"
                      value={formData.end_date}
                      onChange={(e) => setFormData({ ...formData, end_date: e.target.value })}
                      min={formData.start_date}
                      className="pl-10"
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="close_deadline">Strict Deadline</Label>
                  <div className="flex items-center space-x-2">
                    <input
                      id="close_deadline"
                      type="checkbox"
                      checked={formData.close_deadline}
                      onChange={(e) => setFormData({ ...formData, close_deadline: e.target.checked })}
                      className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                    />
                    <span className="text-sm text-muted-foreground">
                      If enabled, task will be automatically marked as "Rejected" when deadline passes
                    </span>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Task Repetition */}
          <Card>
            <CardHeader>
              <CardTitle>Task Repetition</CardTitle>
              <CardDescription>Set up automatic task repetition (optional)</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center space-x-2">
                <input
                  id="is_repeating"
                  type="checkbox"
                  checked={formData.is_repeating}
                  onChange={(e) => setFormData({ ...formData, is_repeating: e.target.checked })}
                  className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                />
                <Label htmlFor="is_repeating">Make this task repeat automatically</Label>
              </div>

              {formData.is_repeating && (
                <div className="space-y-4 pl-6 border-l-2 border-gray-200">
                  <div className="grid gap-4 md:grid-cols-3">
                    <div className="space-y-2">
                      <Label htmlFor="repeat_frequency">Frequency</Label>
                      <select
                        id="repeat_frequency"
                        value={formData.repeat_frequency}
                        onChange={(e) => setFormData({ ...formData, repeat_frequency: e.target.value })}
                        className="w-full px-3 py-2 border rounded-md bg-background"
                      >
                        <option value="">Select frequency</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                      </select>
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="repeat_interval">Every</Label>
                      <Input
                        id="repeat_interval"
                        type="number"
                        min="1"
                        value={formData.repeat_interval}
                        onChange={(e) => setFormData({ ...formData, repeat_interval: parseInt(e.target.value) || 1 })}
                        placeholder="1"
                        className="w-full"
                      />
                      <p className="text-xs text-muted-foreground">
                        {formData.repeat_frequency === 'daily' && 'days'}
                        {formData.repeat_frequency === 'weekly' && 'weeks'}
                        {formData.repeat_frequency === 'monthly' && 'months'}
                        {formData.repeat_frequency === 'yearly' && 'years'}
                      </p>
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="repeat_until">Until (Optional)</Label>
                      <Input
                        id="repeat_until"
                        type="date"
                        value={formData.repeat_until}
                        onChange={(e) => setFormData({ ...formData, repeat_until: e.target.value })}
                        min={formData.start_date}
                        className="w-full"
                      />
                      <p className="text-xs text-muted-foreground">Leave empty to repeat indefinitely</p>
                    </div>
                  </div>

                  <div className="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p className="text-sm text-blue-800">
                      <strong>How it works:</strong> This task will automatically create new instances based on your schedule. 
                      Each new task will inherit all the details, assignments, and settings from this original task.
                    </p>
                  </div>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Template Information */}
          {selectedTemplate && (
            <Card>
              <CardHeader>
                <div className="flex items-center gap-2">
                  <FileText className="h-5 w-5" />
                  <CardTitle>Template Information</CardTitle>
                </div>
                <CardDescription>Template details that will be applied to this task</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid gap-4 md:grid-cols-2">
                  <div className="space-y-2">
                    <Label className="text-sm font-medium">Template Name</Label>
                    <p className="text-sm text-muted-foreground">
                                               {templates.find(t => t.id.toString() === selectedTemplate)?.title}
                    </p>
                  </div>
                  <div className="space-y-2">
                    <Label className="text-sm font-medium">Task Type</Label>
                    <p className="text-sm text-muted-foreground">
                      {taskTypes.find(t => t.id.toString() === formData.task_type_id)?.name}
                    </p>
                  </div>
                </div>
                {templates.find(t => t.id.toString() === selectedTemplate)?.standard_brief && (
                  <div className="space-y-2">
                    <Label className="text-sm font-medium">Standard Brief</Label>
                    <p className="text-sm text-muted-foreground whitespace-pre-wrap">
                      {templates.find(t => t.id.toString() === selectedTemplate)?.standard_brief}
                    </p>
                  </div>
                )}
                {formData.description && (
                  <div className="space-y-2">
                    <Label className="text-sm font-medium">Template Description</Label>
                    <p className="text-sm text-muted-foreground whitespace-pre-wrap">
                      {formData.description}
                    </p>
                  </div>
                )}
              </CardContent>
            </Card>
          )}

          {/* Template Questions */}
          {templateQuestions.length > 0 && (
            <Card>
              <CardHeader>
                <div className="flex items-center gap-2">
                  <HelpCircle className="h-5 w-5" />
                  <CardTitle>Template Questions</CardTitle>
                </div>
                <CardDescription>Please answer the following questions</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                {templateQuestions.map((question) => (
                  <div key={question.id} className="space-y-2">
                    <Label>{question.question_text}</Label>
                    {question.question_type === 'textarea' ? (
                      <Textarea
                        value={questionAnswers[question.id] || ''}
                        onChange={(e) => setQuestionAnswers({
                          ...questionAnswers,
                          [question.id]: e.target.value
                        })}
                        rows={3}
                      />
                    ) : question.question_type === 'select' ? (
                      <select
                        className="w-full px-3 py-2 border rounded-md"
                        value={questionAnswers[question.id] || ''}
                        onChange={(e) => setQuestionAnswers({
                          ...questionAnswers,
                          [question.id]: e.target.value
                        })}
                      >
                        <option value="">Select an option</option>
                        {(question.options || []).map((option, idx) => (
                          <option key={idx} value={option}>{option}</option>
                        ))}
                      </select>
                    ) : question.question_type === 'radio' ? (
                      <div className="space-y-2">
                        {(question.options || []).map((option, idx) => (
                          <label key={idx} className="flex items-center space-x-2">
                            <input
                              type="radio"
                              name={`question-${question.id}`}
                              value={option}
                              checked={questionAnswers[question.id] === option}
                              onChange={(e) => setQuestionAnswers({
                                ...questionAnswers,
                                [question.id]: e.target.value
                              })}
                            />
                            <span>{option}</span>
                          </label>
                        ))}
                      </div>
                    ) : question.question_type === 'checkbox' ? (
                      <div className="flex items-center">
                        <input
                          type="checkbox"
                          checked={questionAnswers[question.id] === 'Yes'}
                          onChange={(e) => setQuestionAnswers({
                            ...questionAnswers,
                            [question.id]: e.target.checked ? 'Yes' : 'No'
                          })}
                          className="mr-2"
                        />
                        <span>Check if applicable</span>
                      </div>
                    ) : (
                      <Input
                        type="text"
                        value={questionAnswers[question.id] || ''}
                        onChange={(e) => setQuestionAnswers({
                          ...questionAnswers,
                          [question.id]: e.target.value
                        })}
                      />
                    )}
                  </div>
                ))}
              </CardContent>
            </Card>
          )}

          {/* Template Checklist */}
          {templateChecklist.length > 0 && (
            <Card>
              <CardHeader>
                <div className="flex items-center gap-2">
                  <CheckSquare className="h-5 w-5" />
                  <CardTitle>Task Checklist</CardTitle>
                </div>
                <CardDescription>This task will include the following checklist items</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-2 text-sm">
                  {templateChecklist.map((item, index) => (
                    <div key={index} className="flex items-center gap-2">
                      <div className="w-4 h-4 border rounded flex items-center justify-center">
                        <CheckSquare className="h-3 w-3 text-muted-foreground" />
                      </div>
                      <span>{item}</span>
                    </div>
                  ))}
                </div>
                <p className="text-xs text-muted-foreground mt-4">
                  These items will be added to the task and can be checked off as completed.
                </p>
              </CardContent>
            </Card>
          )}

          <Card>
            <CardHeader>
              <CardTitle>Assign Users</CardTitle>
              <CardDescription>Select users to assign to this task</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid gap-3 md:grid-cols-2">
                {users.map((user) => (
                  <label
                    key={user.id}
                    className="flex items-center space-x-3 cursor-pointer p-3 rounded-lg border hover:bg-accent"
                  >
                    <input
                      type="checkbox"
                      checked={formData.user_ids.includes(user.id)}
                      onChange={() => toggleUserSelection(user.id)}
                      className="rounded border-gray-300"
                    />
                    <div className="flex-1">
                      <p className="text-sm font-medium">
                        {user.first_name} {user.last_name}
                      </p>
                      <p className="text-xs text-muted-foreground">{user.email}</p>
                    </div>
                  </label>
                ))}
              </div>
            </CardContent>
          </Card>



          {/* Actions */}
          <div className="flex justify-end gap-4">
            <Button
              type="button"
              variant="outline"
              onClick={() => router.push('/tasks')}
              disabled={loading}
            >
              Cancel
            </Button>
            <Button type="submit" disabled={loading}>
              {loading ? (
                <>Creating...</>
              ) : (
                <>
                  <Save className="h-4 w-4 mr-2" />
                  Create Task
                </>
              )}
            </Button>
          </div>
        </form>
      </div>
    </MainLayout>
  );
}
