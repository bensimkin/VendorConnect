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
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Save, Trash2, Plus, X, FileText } from 'lucide-react';
import { apiClient } from '@/lib/api-client';

interface Task {
  id: number;
  title: string;
  description?: string;
  status?: {
    id: number;
    name: string;
  };
  priority?: {
    id: number;
    name: string;
  };
  assigned_to?: {
    id: number;
    first_name: string;
    last_name: string;
  };
  client?: {
    id: number;
    name: string;
  };
  project?: {
    id: number;
    name: string;
  };
  due_date?: string;
  task_type?: {
    id: number;
    task_type: string;
  };
  template?: {
    id: number;
    template_name: string;
  };
  close_deadline?: boolean;
}

interface User {
  id: number;
  first_name: string;
  last_name: string;
}

interface Client {
  id: number;
  name: string;
}

interface Project {
  id: number;
  name: string;
}

interface TaskType {
  id: number;
  task_type: string;
}

interface Template {
  id: number;
  template_name: string;
  standard_brief?: string;
  description?: string;
  deliverable_quantity?: number;
}

interface Status {
  id: number;
  name: string;
}

interface Priority {
  id: number;
  name: string;
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
    status_id: 0,
    priority_id: 0,
    user_ids: [] as number[],
    client_ids: [] as number[],
    project_id: 0,
    end_date: '',
    task_type_id: 0,
    close_deadline: false,
  });

  useEffect(() => {
    fetchData();
  }, [taskId]);

  const fetchData = async () => {
    try {
      console.log('Starting fetchData for taskId:', taskId);
      console.log('Auth token:', localStorage.getItem('auth_token'));
      
      // Fetch task details
      const taskRes = await apiClient.get(`/tasks/${taskId}`);
      console.log('Task API response:', taskRes.data);
      
      if (!taskRes.data || !taskRes.data.data) {
        console.error('Invalid API response structure:', taskRes);
        throw new Error('Invalid API response');
      }
      
      const taskData: Task = taskRes.data.data;
      console.log('Task object:', taskData);
      
      if (!taskData) {
        throw new Error('Task not found');
      }

      setTask(taskData);
            setFormData({
        title: taskData?.title || '',
        description: taskData?.description || '',
        status_id: taskData?.status?.id || 0,
        priority_id: taskData?.priority?.id || 0,
        user_ids: taskData?.assigned_to?.id ? [taskData.assigned_to.id] : [],
        client_ids: taskData?.client?.id ? [taskData.client.id] : [],
        project_id: taskData?.project?.id || 0,
        end_date: taskData?.due_date ? taskData.due_date.split('T')[0] : '',
        task_type_id: taskData?.task_type?.id || 0,
        close_deadline: taskData?.close_deadline || false,
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
      setTaskTypes(taskTypesRes.data.data?.data || taskTypesRes.data.data || []);
      setTemplates(templatesRes.data.data?.data || templatesRes.data.data || []);
      setStatuses(statusesRes.data.data?.data || statusesRes.data.data || []);
      setPriorities(prioritiesRes.data.data?.data || prioritiesRes.data.data || []);

      // Load template questions if template exists
      if (task.template?.id) {
        const questionsRes = await apiClient.get(`/task-brief-questions?template_id=${task.template.id}`);
        const questions = questionsRes.data.data?.data || questionsRes.data.data || [];
        setTemplateQuestions(questions);

        // Load existing answers
        const answersRes = await apiClient.get(`/tasks/${taskId}/question-answers`);
        const answers: QuestionAnswer[] = answersRes.data.data?.data || answersRes.data.data || [];
        const answersMap: Record<number, string> = {};
        answers.forEach(answer => {
          answersMap[answer.question_id] = answer.question_answer;
        });
        setQuestionAnswers(answersMap);
      }

      // Load checklist if template exists
      if (task.template?.id) {
        const checklistRes = await apiClient.get(`/task-brief-checklists?template_id=${task.template.id}`);
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

        // Load checklist completion status
        const checklistStatusRes = await apiClient.get(`/tasks/${taskId}/checklist-status`);
        const checklistStatus = checklistStatusRes.data.data?.data || checklistStatusRes.data.data || [];
        const completedMap: Record<number, boolean> = {};
        checklistStatus.forEach((item: any) => {
          completedMap[item.item_index] = item.completed;
        });
        setChecklistCompleted(completedMap);
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
      // Update task details
      await apiClient.put(`/tasks/${taskId}`, formData);

      // Save question answers
      for (const [questionId, answer] of Object.entries(questionAnswers)) {
        if (answer.trim()) {
          await apiClient.post(`/tasks/${taskId}/question-answers`, {
            question_id: parseInt(questionId),
            question_answer: answer,
          });
        }
      }

      // Save checklist completion status
      for (const [index, completed] of Object.entries(checklistCompleted)) {
        await apiClient.post(`/tasks/${taskId}/checklist-status`, {
          item_index: parseInt(index),
          completed: completed,
        });
      }

      toast.success('Task updated successfully');
      router.push(`/tasks/${taskId}`);
    } catch (error: any) {
      console.error('Failed to update task:', error);
      toast.error('Failed to update task');
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
                    value={formData.status_id}
                    onChange={(e) => setFormData({ ...formData, status_id: parseInt(e.target.value) })}
                    className="w-full px-3 py-2 border rounded-md"
                  >
                    <option value="">Select Status</option>
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
                    onChange={(e) => setFormData({ ...formData, priority_id: parseInt(e.target.value) })}
                    className="w-full px-3 py-2 border rounded-md"
                  >
                    <option value="">Select Priority</option>
                    {priorities.map((priority) => (
                      <option key={priority.id} value={priority.id}>
                        {priority.name}
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
                        {client.name}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="project">Project</Label>
                  <select
                    id="project"
                    value={formData.project_id}
                    onChange={(e) => setFormData({ ...formData, project_id: parseInt(e.target.value) })}
                    className="w-full px-3 py-2 border rounded-md"
                  >
                    <option value="">Select Project</option>
                    {projects.map((project) => (
                      <option key={project.id} value={project.id}>
                        {project.name}
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
                      checked={formData.close_deadline}
                      onChange={(e) => setFormData({ ...formData, close_deadline: e.target.checked })}
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
                    value={formData.task_type_id}
                    onChange={(e) => setFormData({ ...formData, task_type_id: parseInt(e.target.value) })}
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
                      {task.template.template_name}
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
