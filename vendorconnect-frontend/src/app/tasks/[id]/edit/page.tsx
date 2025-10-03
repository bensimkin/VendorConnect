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
const getClientDisplayName = (client: { first_name: string; last_name: string; company?: string }) => {
  const fullName = `${client.first_name || ''} ${client.last_name || ''}`.trim();
  return client.company ? `${fullName} (${client.company})` : fullName;
};

interface Task {
  id: number;
  title: string;
  description?: string;
  standard_brief?: string;
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
  is_repeating?: boolean;
  repeat_frequency?: string;
  repeat_interval?: number;
  skip_weekends?: boolean;
  repeat_until?: string;
  repeat_start?: string;
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
    standard_brief: '',
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
    is_repeating: false,
    repeat_frequency: '',
    repeat_interval: 1,
    skip_weekends: true,
    repeat_until: '',
    repeat_start: '',
  });

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
           standard_brief: taskData?.standard_brief || '',
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
           is_repeating: taskData?.is_repeating || false,
           repeat_frequency: taskData?.repeat_frequency || '',
           repeat_interval: taskData?.repeat_interval || 1,
           skip_weekends: taskData?.skip_weekends ?? true,
           repeat_until: taskData?.repeat_until ? taskData.repeat_until.split('T')[0] : '',
           repeat_start: taskData?.repeat_start ? taskData.repeat_start.split('T')[0] : '',
         });
         

      // Fetch dropdown data
      const [usersRes, clientsRes, projectsRes, taskTypesRes, templatesRes, statusesRes, prioritiesRes] = await Promise.all([
        apiClient.get('/users'),
        apiClient.get('/clients'),
        apiClient.get('/projects?per_page=all'),
        apiClient.get('/task-types?per_page=all'),
        apiClient.get('/task-brief-templates'),
        apiClient.get('/statuses?per_page=all'),
        apiClient.get('/priorities?per_page=all'),
      ]);

      // Helper to consistently extract array data from API responses
      const extractData = (res: any) => res?.data?.data?.data || res?.data?.data || res?.data || [];

      const statusesData = extractData(statusesRes);
      const prioritiesData = extractData(prioritiesRes);
      const projectsData = extractData(projectsRes);
      const clientsData = extractData(clientsRes);
      const usersData = extractData(usersRes);
      const taskTypesData = extractData(taskTypesRes);
      const templatesData = extractData(templatesRes);

      setUsers(usersData);
      setClients(clientsData);
      setProjects(projectsData);
      setTaskTypes(taskTypesData);
      setTemplates(templatesData);
      setStatuses(statusesData);
      setPriorities(prioritiesData);

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

    // Validate repeating task fields
    if (formData.is_repeating) {
      if (!formData.repeat_frequency) {
        toast.error('Please select a repeat frequency');
        return;
      }
      if (formData.repeat_start && formData.end_date && formData.repeat_start < formData.end_date) {
        toast.error('Repeat start date cannot be before the task end date');
        return;
      }
      if (formData.repeat_until && formData.repeat_start && formData.repeat_until < formData.repeat_start) {
        toast.error('Repeat until date cannot be before the repeat start date');
        return;
      }
    }

    setSaving(true);
    try {
      const payload: Record<string, any> = {
        title: formData.title,
        description: formData.description,
        standard_brief: formData.standard_brief,
        note: formData.note,
        status_id: formData.status_id || 15, // Default to "Pending" status
        priority_id: formData.priority_id || 2, // Default to "Medium" priority
        project_id: formData.project_id,
        task_type_id: formData.task_type_id,
        user_ids: formData.user_ids,
        // client_ids: formData.client_ids, // Removed - no direct client-task relationship
        end_date: formData.end_date,
        close_deadline: formData.close_deadline,
        deliverable_quantity: formData.deliverable_quantity,
        is_repeating: formData.is_repeating,
        repeat_frequency: formData.repeat_frequency || null,
        repeat_interval: formData.repeat_interval,
        skip_weekends: formData.skip_weekends,
        repeat_until: formData.repeat_until || null,
        repeat_start: formData.repeat_start || null,
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
                    {/* Debug: Priorities count: {priorities.length} */}
                    <option value="">Select Priority</option>
                    {priorities.map((priority) => (
                      <option key={priority.id} value={priority.id}>
                        {priority.title || `Priority ${priority.id}`}
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
                    {/* Debug: Projects count: {projects.length} */}
                    <option value="">Select Project</option>
                    {projects.map((project) => (
                      <option key={project.id} value={project.id}>
                        {project.title || `Project ${project.id}`}
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

              {/* Repeatable Task Settings */}
              <div className="space-y-4 border-t pt-4">
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
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pl-6 border-l-2 border-gray-200">
                    <div className="space-y-2">
                      <Label htmlFor="repeat_frequency">Frequency</Label>
                      <select
                        id="repeat_frequency"
                        value={formData.repeat_frequency}
                        onChange={(e) => setFormData({ ...formData, repeat_frequency: e.target.value })}
                        className="w-full px-3 py-2 border rounded-md"
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
                      <Label htmlFor="repeat_start">Start (Optional)</Label>
                      <Input
                        id="repeat_start"
                        type="date"
                        value={formData.repeat_start}
                        onChange={(e) => setFormData({ ...formData, repeat_start: e.target.value })}
                        min={formData.end_date}
                      />
                      <p className="text-xs text-muted-foreground">When to start repeating</p>
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="repeat_until">Until (Optional)</Label>
                      <Input
                        id="repeat_until"
                        type="date"
                        value={formData.repeat_until}
                        onChange={(e) => setFormData({ ...formData, repeat_until: e.target.value })}
                        min={formData.repeat_start || formData.end_date}
                      />
                      <p className="text-xs text-muted-foreground">Leave empty to repeat indefinitely</p>
                    </div>
                  </div>

                  <div className="flex items-center space-x-2 pl-6 mt-4">
                    <input
                      type="checkbox"
                      id="skip_weekends"
                      checked={formData.skip_weekends}
                      onChange={(e) => setFormData({ ...formData, skip_weekends: e.target.checked })}
                      className="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                    <Label htmlFor="skip_weekends" className="text-sm font-normal cursor-pointer">
                      Skip weekends (automatically move tasks that fall on Saturday/Sunday to Monday)
                    </Label>
                  </div>
                )}
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
                <Label htmlFor="standard_brief">Standard Brief</Label>
                <Textarea
                  id="standard_brief"
                  value={formData.standard_brief}
                  onChange={(e) => setFormData({ ...formData, standard_brief: e.target.value })}
                  placeholder="Enter standard brief for this task"
                  rows={4}
                />
                <p className="text-xs text-muted-foreground">Standard brief that will be applied to this task</p>
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
