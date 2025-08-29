'use client';

import { useState, useEffect } from 'react';
import { useRouter, useParams } from 'next/navigation';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Save, Trash2, Plus, X, FileText } from 'lucide-react';
import { apiClient } from '@/lib/api-client';
import { toast } from 'react-hot-toast';

// Helper function to get client display name
const getClientDisplayName = (client: { first_name: string; last_name: string; name?: string }) => {
  return client.name || `${client.first_name} ${client.last_name}`.trim();
};

interface Task {
  id: number;
  title: string;
  description?: string;
  note?: string;
  deliverable_quantity?: number;
  status_id?: number;
  priority_id?: number;
  project_id?: number;
  task_type_id?: number;
  status?: {
    id: number;
    title: string;
  };
  priority?: {
    id: number;
    title: string;
  };
  users?: Array<{
    id: number;
    first_name: string;
    last_name: string;
  }>;
  clients?: Array<{
    id: number;
    first_name: string;
    last_name: string;
    company?: string;
  }>;
  project?: {
    id: number;
    title: string;
  };
  end_date?: string;
  taskType?: {
    id: number;
    task_type: string;
  };
  template?: {
    id: number;
    title: string;
    standard_brief?: string;
    description?: string;
    deliverable_quantity?: number;
  };
  close_deadline?: boolean;
  question_answers?: Array<{
    question_id: number;
    question_answer: string;
  }>;
  checklist_answers?: Array<{
    completed: boolean;
  }>;
}

interface User {
  id: number;
  first_name: string;
  last_name: string;
}

interface Client {
  id: number;
  first_name: string;
  last_name: string;
  name?: string; // For backward compatibility
  company?: string;
}

interface Project {
  id: number;
  title: string;
}

interface TaskType {
  id: number;
  task_type: string;
}

interface Template {
  id: number;
      title: string;
  standard_brief?: string;
  description?: string;
  deliverable_quantity?: number;
}

interface Status {
  id: number;
  title: string;
}

interface Priority {
  id: number;
  title: string;
}

interface TemplateQuestion {
  id: number;
  question_text: string;
  question_type: 'text' | 'textarea' | 'select' | 'checkbox' | 'radio';
  options?: string[];
}

interface QuestionAnswer {
  id: number;
  question_id: number;
  question_answer: string;
  briefQuestions: TemplateQuestion;
}

export default function EditTaskPage() {
  const router = useRouter();
  const params = useParams();
  const taskId = params.id as string;

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [task, setTask] = useState<Task | null>(null);
  const [users, setUsers] = useState<User[]>([]);
  const [clients, setClients] = useState<Client[]>([]);
  const [projects, setProjects] = useState<Project[]>([]);
  const [taskTypes, setTaskTypes] = useState<TaskType[]>([]);
  const [templates, setTemplates] = useState<Template[]>([]);
  const [statuses, setStatuses] = useState<Status[]>([]);
  const [priorities, setPriorities] = useState<Priority[]>([]);
  const [templateQuestions, setTemplateQuestions] = useState<TemplateQuestion[]>([]);
  const [questionAnswers, setQuestionAnswers] = useState<Record<number, string>>({});
  const [checklistItems, setChecklistItems] = useState<string[]>([]);
  const [checklistCompleted, setChecklistCompleted] = useState<Record<number, boolean>>({});

  const [formData, setFormData] = useState({
    title: '',
    description: '',
    note: '',
    status_id: null as number | null,
    priority_id: null as number | null,
    user_ids: [] as number[],
    client_ids: [] as number[],
    project_id: null as number | null,
    end_date: '',
    task_type_id: null as number | null,
    close_deadline: 0,
    deliverable_quantity: 1,
  });

  // Debug: Log form data changes
  useEffect(() => {
  }, [formData]);

  useEffect(() => {
    fetchData();
  }, [taskId]);

  const fetchData = async () => {
    try {
      
      // Fetch task details
      const taskRes = await apiClient.get(`/tasks/${taskId}`);
      
      if (!taskRes.data || !taskRes.data.data) {
        console.error('Invalid API response structure:', taskRes);
        throw new Error('Invalid API response');
      }
      
      const taskData: Task = taskRes.data.data;
      
      if (!taskData) {
        throw new Error('Task not found');
      }

      setTask(taskData);
      
      
      setFormData({
           title: taskData?.title || '',
           description: taskData?.description || '',
           note: taskData?.note || '',
           status_id: taskData?.status?.id || null,
           priority_id: taskData?.priority?.id || null,
           user_ids: taskData?.users?.map(user => user.id) || [],
           client_ids: taskData?.clients?.map(client => client.id) || [],
           project_id: taskData?.project?.id || null,
           end_date: taskData?.end_date ? taskData.end_date.split('T')[0] : '',
           task_type_id: taskData?.taskType?.id || null,
           close_deadline: taskData?.close_deadline === true ? 1 : 0,
           deliverable_quantity: taskData?.deliverable_quantity || 1,
         });
         

      // Fetch dropdown data
      const [usersRes, clientsRes, projectsRes, taskTypesRes, templatesRes, statusesRes, prioritiesRes] = await Promise.all([
        apiClient.get('/users'),
        apiClient.get('/clients'),
        apiClient.get('/projects'),
        apiClient.get('/task-types?per_page=all'),
        apiClient.get('/task-brief-templates'),
        apiClient.get('/statuses'),
        apiClient.get('/priorities'),
      ]);
      
      setUsers(usersRes.data.data?.data || usersRes.data.data || []);
      setClients(clientsRes.data.data?.data || clientsRes.data.data || []);
      setProjects(projectsRes.data.data?.data || projectsRes.data.data || []);
      setTaskTypes(taskTypesRes.data.data || []);
      setTemplates(templatesRes.data.data || []);
      setStatuses(statusesRes.data.data?.data || statusesRes.data.data || []);
      setPriorities(prioritiesRes.data.data?.data || prioritiesRes.data.data || []);

      // Load template questions if template exists
      if (taskData.template?.id) {
        const questionsRes = await apiClient.get(`/task-brief-questions?template_id=${taskData.template.id}`);
        const questions = questionsRes.data.data?.data || questionsRes.data.data || [];
        setTemplateQuestions(questions);

        // Load existing answers from task data
        if (taskData.question_answers && taskData.question_answers.length > 0) {
          const answersMap: Record<number, string> = {};
          taskData.question_answers.forEach((qa: any) => {
            answersMap[qa.question_id] = qa.question_answer;
          });
          setQuestionAnswers(answersMap);
        }
      }

      // Load checklist if template exists
      if (taskData.template?.id) {
        const checklistRes = await apiClient.get(`/task-brief-checklists?template_id=${taskData.template.id}`);
        const checklistData = checklistRes.data.data?.data || checklistRes.data.data || [];
        if (checklistData.length > 0 && checklistData[0].checklist) {
          const checklist = checklistData[0].checklist;
          if (Array.isArray(checklist)) {
            setChecklistItems(checklist);
          } else if (typeof checklist === 'string') {
            try {
              const parsed = JSON.parse(checklist);
              setChecklistItems(Array.isArray(parsed) ? parsed : [checklist]);
            } catch {
              setChecklistItems([checklist]);
            }
          }
        }

        // Load checklist completion status from task data
        if (taskData.checklist_answers && taskData.checklist_answers.length > 0) {
          const completedMap: Record<number, boolean> = {};
          taskData.checklist_answers.forEach((ca: any, index: number) => {
            completedMap[index] = ca.completed || false;
          });
          setChecklistCompleted(completedMap);
        }
      }

    } catch (error: any) {
      console.error('Failed to fetch task data:', error);
      console.error('Error response:', error.response?.data);
      console.error('Error status:', error.response?.status);
      toast.error('Failed to load task');
      router.push('/tasks');
    } finally {
      setLoading(false);
    }
  };



  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.title.trim()) {
      toast.error('Task title is required');
      return;
    }

    setSaving(true);
    try {
      const payload: Record<string, any> = {
        title: formData.title,
        description: formData.description,
        note: formData.note,
        status_id: formData.status_id || 15, // Default to "Pending" status
        priority_id: formData.priority_id || 2, // Default to "Medium" priority
        project_id: formData.project_id,
        task_type_id: formData.task_type_id,
        user_ids: formData.user_ids,
        client_ids: formData.client_ids,
        end_date: formData.end_date,
        close_deadline: formData.close_deadline,
        deliverable_quantity: formData.deliverable_quantity,
      };

      await apiClient.put(`/tasks/${taskId}`, payload);
      toast.success('Task updated successfully');
      router.push(`/tasks/${taskId}`);
    } catch (error: any) {
      console.error('Failed to update task:', error);
      toast.error(error.response?.data?.message || 'Failed to update task');
    } finally {
      setSaving(false);
    }
  };

  const handleTemplateChange = async (templateId: number) => {
    if (templateId) {
      try {
        // Load template questions
        const questionsRes = await apiClient.get(`/task-brief-questions?template_id=${templateId}`);
        const questions = questionsRes.data.data?.data || questionsRes.data.data || [];
        setTemplateQuestions(questions);
        setQuestionAnswers({});
        
        // Load checklist
        const checklistRes = await apiClient.get(`/task-brief-checklists?template_id=${templateId}`);
        const checklistData = checklistRes.data.data?.data || checklistRes.data.data || [];
        if (checklistData.length > 0 && checklistData[0].checklist) {
          const checklist = checklistData[0].checklist;
          if (Array.isArray(checklist)) {
            setChecklistItems(checklist);
          } else if (typeof checklist === 'string') {
            try {
              const parsed = JSON.parse(checklist);
              setChecklistItems(Array.isArray(parsed) ? parsed : [checklist]);
            } catch {
              setChecklistItems([checklist]);
            }
          }
        } else {
          setChecklistItems([]);
        }
        setChecklistCompleted({});
      } catch (error) {
        console.error('Failed to load template data:', error);
      }
    } else {
      setTemplateQuestions([]);
      setChecklistItems([]);
      setQuestionAnswers({});
      setChecklistCompleted({});
    }
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading task...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  // Debug: Check if data is loaded

  // Debug: Log current state before render

  // Test API calls
  const testAPIs = async () => {
    try {
      const token = localStorage.getItem('auth_token');
      
      const taskRes = await apiClient.get(`/tasks/${taskId}`);
      
      const statusesRes = await apiClient.get('/statuses');
      
      const prioritiesRes = await apiClient.get('/priorities');
      
      const projectsRes = await apiClient.get('/projects');
    } catch (error) {
      console.error('API test error:', error);
    }
  };

  return (
    <MainLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <Button
              variant="ghost"
              size="sm"
              onClick={() => router.push(`/tasks/${taskId}`)}
            >
              <ArrowLeft className="h-4 w-4 mr-2" />
              Back to Task
            </Button>
            <h1 className="text-2xl font-bold">Edit Task</h1>
            <Button onClick={testAPIs} variant="outline" size="sm">
              Test APIs
            </Button>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Basic Task Information */}
          <Card>
            <CardHeader>
              <CardTitle>Task Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="title">Title *</Label>
                  <Input
                    id="title"
                    value={formData.title}
                    onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="status">Status</Label>
                  <select
                    id="status"
                    value={formData.status_id || ''}
                    onChange={(e) => setFormData({ ...formData, status_id: parseInt(e.target.value) || null })}
                    className="w-full px-3 py-2 border rounded-md"
                  >
                    <option value="">Select Status</option>
                    {statuses.map((status) => (
                      <option key={status.id} value={status.id}>
                        {status.title}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="priority">Priority</Label>
                  <select
                    id="priority"
                    value={formData.priority_id || ''}
                    onChange={(e) => setFormData({ ...formData, priority_id: parseInt(e.target.value) || null })}
                    className="w-full px-3 py-2 border rounded-md"
                  >
                    <option value="">Select Priority</option>
                    {priorities.map((priority) => (
                      <option key={priority.id} value={priority.id}>
                        {priority.title}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="assigned_to">Assigned To</Label>
                  <select
                    id="assigned_to"
                    value={formData.user_ids[0] || ''}
                    onChange={(e) => setFormData({ 
                      ...formData, 
                      user_ids: e.target.value ? [parseInt(e.target.value)] : [] 
                    })}
                    className="w-full px-3 py-2 border rounded-md"
                  >
                    <option value="">Select User</option>
                    {users.map((user) => (
                      <option key={user.id} value={user.id}>
                        {user.first_name} {user.last_name}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="client">Client</Label>
                  <select
                    id="client"
                    value={formData.client_ids[0] || ''}
                    onChange={(e) => setFormData({ 
                      ...formData, 
                      client_ids: e.target.value ? [parseInt(e.target.value)] : [] 
                    })}
                    className="w-full px-3 py-2 border rounded-md"
                  >
                    <option value="">Select Client</option>
                    {clients.map((client) => (
                      <option key={client.id} value={client.id}>
                        {getClientDisplayName(client)}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="project">Project</Label>
                  <select
                    id="project"
                    value={formData.project_id || ''}
                    onChange={(e) => setFormData({ ...formData, project_id: parseInt(e.target.value) || null })}
                    className="w-full px-3 py-2 border rounded-md"
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
                  <Label htmlFor="due_date">Due Date</Label>
                  <Input
                    id="due_date"
                    type="date"
                    value={formData.end_date}
                    onChange={(e) => setFormData({ ...formData, end_date: e.target.value })}
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="close_deadline">Strict Deadline</Label>
                  <div className="flex items-center space-x-2">
                    <input
                      id="close_deadline"
                      type="checkbox"
                      checked={formData.close_deadline === 1}
                      onChange={(e) => setFormData({ ...formData, close_deadline: e.target.checked ? 1 : 0 })}
                      className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                    />
                    <span className="text-sm text-muted-foreground">
                      If enabled, task will be automatically marked as "Rejected" when deadline passes
                    </span>
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="task_type">Task Type</Label>
                  <select
                    id="task_type"
                    value={formData.task_type_id || ''}
                    onChange={(e) => setFormData({ ...formData, task_type_id: parseInt(e.target.value) || null })}
                    className="w-full px-3 py-2 border rounded-md"
                  >
                    <option value="">Select Task Type</option>
                    {taskTypes.map((type) => (
                      <option key={type.id} value={type.id}>
                        {type.task_type}
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


              </div>

              <div className="space-y-2">
                <Label htmlFor="description">Description</Label>
                <Textarea
                  id="description"
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  rows={4}
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="note">Notes</Label>
                <Textarea
                  id="note"
                  value={formData.note}
                  onChange={(e) => setFormData({ ...formData, note: e.target.value })}
                  placeholder="Enter task notes, brief, or additional instructions"
                  rows={4}
                />
                <p className="text-xs text-muted-foreground">Additional instructions, brief, or notes for the task</p>
              </div>
            </CardContent>
          </Card>

          {/* Template Information */}
          {task?.template && (
            <Card>
              <CardHeader>
                <div className="flex items-center gap-2">
                  <FileText className="h-5 w-5" />
                  <CardTitle>Template Information</CardTitle>
                </div>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid gap-4 md:grid-cols-2">
                  <div className="space-y-2">
                    <Label className="text-sm font-medium">Template Name</Label>
                    <p className="text-sm text-muted-foreground">
                                              {task.template.title}
                    </p>
                  </div>
                  <div className="space-y-2">
                    <Label className="text-sm font-medium">Template ID</Label>
                    <p className="text-sm text-muted-foreground">
                      #{task.template.id}
                    </p>
                  </div>
                </div>
                {task.template.standard_brief && (
                  <div className="space-y-2">
                    <Label className="text-sm font-medium">Standard Brief</Label>
                    <p className="text-sm text-muted-foreground whitespace-pre-wrap">
                      {task.template.standard_brief}
                    </p>
                  </div>
                )}
                {task.template.description && (
                  <div className="space-y-2">
                    <Label className="text-sm font-medium">Template Description</Label>
                    <p className="text-sm text-muted-foreground whitespace-pre-wrap">
                      {task.template.description}
                    </p>
                  </div>
                )}
                {task.template.deliverable_quantity && task.template.deliverable_quantity > 1 && (
                  <div className="space-y-2">
                    <Label className="text-sm font-medium">Default Quantity</Label>
                    <p className="text-sm text-muted-foreground">
                      {task.template.deliverable_quantity}
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
                <CardTitle>Template Questions</CardTitle>
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

          {/* Checklist */}
          {checklistItems.length > 0 && (
            <Card>
              <CardHeader>
                <CardTitle>Checklist</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {checklistItems.map((item, index) => (
                  <div key={index} className="flex items-center space-x-2">
                    <input
                      type="checkbox"
                      checked={checklistCompleted[index] || false}
                      onChange={(e) => setChecklistCompleted({
                        ...checklistCompleted,
                        [index]: e.target.checked
                      })}
                      className="mr-2"
                    />
                    <span className={checklistCompleted[index] ? 'line-through text-muted-foreground' : ''}>
                      {item}
                    </span>
                  </div>
                ))}
              </CardContent>
            </Card>
          )}

          {/* Submit Button */}
          <div className="flex justify-end space-x-4">
            <Button
              type="button"
              variant="outline"
              onClick={() => router.push(`/tasks/${taskId}`)}
            >
              Cancel
            </Button>
            <Button type="submit" disabled={saving}>
              {saving ? 'Saving...' : 'Save Changes'}
            </Button>
          </div>
        </form>
      </div>
    </MainLayout>
  );
}
