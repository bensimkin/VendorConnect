'use client';

import { useState, useEffect } from 'react';
import { useParams, useRouter } from 'next/navigation';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import apiClient from '@/lib/api-client';
import getFileUrl from '@/lib/file-utils';
import { ArrowLeft, Edit, Trash2, Calendar, User, Tag, MessageSquare, Paperclip, Clock, CheckCircle, AlertCircle, Plus, Edit3, AlertTriangle, FileText, Eye, X } from 'lucide-react';
import { format } from 'date-fns';
import { toast } from 'react-hot-toast';
import { useAuthStore } from '@/lib/auth-store';
import ClientBriefSection from '@/components/tasks/ClientBriefSection';
import { LinkifyContent } from '@/lib/utils/text-utils';

interface TaskDetail {
  id: number;
  title: string;
  description?: string;
  standard_brief?: string;
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
    email: string;
  }>;
  clients?: Array<{
    id: number;
    name: string;
    first_name?: string;
    last_name?: string;
  }>;
  project?: {
    id: number;
    title: string;
  };
  start_date?: string;
  end_date?: string;
  close_deadline?: boolean;
  created_at: string;
  updated_at: string;
  created_by?: {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
  };
  tags?: Array<{ id: number; name: string }>;
  note?: string;
  deliverable_quantity?: number;
  template_id?: number;
  template_questions?: any[];
  template_checklist?: any[];
  template_standard_brief?: string;
  template_description?: string;
  template_deliverable_quantity?: number;
  template?: {
    id: number;
    title: string;
    standard_brief?: string;
    description?: string;
    deliverable_quantity?: number;
  };
  question_answers?: Array<{
    id: number;
    question_id: number;
    question_answer: string;
    brief_questions: {
      id: number;
      question_text: string;
      question_type: string;
      options?: string[];
    };
  }>;
  checklist_answers?: Array<{
    id: number;
    checklist_id: number;
    completed: boolean;
    notes: string;
  }>;
  has_deliverable?: boolean;
  deliverable_title?: string;
  deliverable_description?: string;
  deliverable_type?: string;
  deliverable_completed_at?: string;
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

interface Comment {
  id: string;
  message_text: string;
  created_at: string;
  sender: {
    id: number;
    first_name: string;
    last_name: string;
  };
}

export default function TaskDetailPage() {
  const params = useParams();
  const router = useRouter();
  const { user } = useAuthStore();
  const [task, setTask] = useState<TaskDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [comment, setComment] = useState('');
  const [submittingComment, setSubmittingComment] = useState(false);
  const [templateQuestions, setTemplateQuestions] = useState<TemplateQuestion[]>([]);
  const [questionAnswers, setQuestionAnswers] = useState<QuestionAnswer[]>([]);
  const [checklistItems, setChecklistItems] = useState<string[]>([]);
  const [checklistCompleted, setChecklistCompleted] = useState<Record<number, boolean>>({});
  const [checklistId, setChecklistId] = useState<number | null>(null);
  const [showDeliverableForm, setShowDeliverableForm] = useState(false);
  const [showDeliverableModal, setShowDeliverableModal] = useState(false);
  const [selectedDeliverable, setSelectedDeliverable] = useState<any>(null);
  const [deliverableForm, setDeliverableForm] = useState({
    title: '',
    description: '',
    type: 'other',
    google_link: '',
    external_link: '',
  });
  const [deliverableFiles, setDeliverableFiles] = useState<File[]>([]);
  const [submittingDeliverable, setSubmittingDeliverable] = useState(false);
  const [deliverables, setDeliverables] = useState<any[]>([]);
  const [comments, setComments] = useState<Comment[]>([]);
  const [editingStatus, setEditingStatus] = useState(false);
  const [editingPriority, setEditingPriority] = useState(false);
  const [updatingStatus, setUpdatingStatus] = useState(false);
  const [updatingPriority, setUpdatingPriority] = useState(false);
  const [statuses, setStatuses] = useState<any[]>([]);
  const [priorities, setPriorities] = useState<any[]>([]);

  useEffect(() => {
    if (params.id) {
      fetchTaskDetail(params.id as string);
    }
  }, [params.id]);

  const fetchTaskDetail = async (id: string) => {
    try {
      
      // Fetch task details
      const response = await apiClient.get(`/tasks/${id}`);
      const taskData = response.data.data;
      setTask(taskData);

      // Fetch statuses and priorities for inline editing (only once)
      if (statuses.length === 0 || priorities.length === 0) {
        const [statusesRes, prioritiesRes] = await Promise.all([
          apiClient.get('/statuses'),
          apiClient.get('/priorities')
        ]);
        
        setStatuses(statusesRes.data.data || []);
        setPriorities(prioritiesRes.data.data || []);
      }

      // Load template questions from task data
      if (taskData.template_questions && taskData.template_questions.length > 0) {
        
        const questions: TemplateQuestion[] = [];
        taskData.template_questions.forEach((q: any) => {
          questions.push({
            id: q.id,
            question_text: q.question_text,
            question_type: q.question_type,
            options: q.options,
          });
        });
        
        setTemplateQuestions(questions);
        
        // Load saved question answers
        try {
          const questionAnswersResponse = await apiClient.get(`/tasks/${id}/question-answers`);
          
          const savedAnswers = questionAnswersResponse.data.data;
          if (savedAnswers && savedAnswers.length > 0) {
            const answers: QuestionAnswer[] = [];
            savedAnswers.forEach((qa: any) => {
              const question = questions.find(q => q.id === qa.question_id);
              if (question) {
                answers.push({
                  id: qa.id,
                  question_id: qa.question_id,
                  question_answer: qa.question_answer,
                  briefQuestions: question,
                });
              }
            });
            setQuestionAnswers(answers);
          }
          } catch (error) {
            console.error('Failed to load question answers:', error);
          }

                   // Load checklist items from task template data
             if (task?.template_checklist && task.template_checklist.length > 0) {
               
               const checklistItems: string[] = [];
               const completedMap: Record<number, boolean> = {};
               
               // Parse checklist items from the first checklist object
               const checklistData = task.template_checklist[0];
               if (checklistData && checklistData.checklist) {
                 // Store the checklist ID for later use
                 setChecklistId(checklistData.id);
                 
                 try {
                   const parsedChecklist = JSON.parse(checklistData.checklist);
                   if (Array.isArray(parsedChecklist)) {
                     parsedChecklist.forEach((item: any, index: number) => {
                       checklistItems.push(item.text || item || `Checklist item ${index + 1}`);
                       completedMap[index] = false; // Default to unchecked
                     });
                   }
                 } catch (e) {
                   console.error('Failed to parse checklist data:', e);
                 }
               }
               
               setChecklistItems(checklistItems);
               setChecklistCompleted(completedMap);
               
               // Load saved checklist states
               try {
                 const checklistStatusResponse = await apiClient.get(`/tasks/${id}/checklist-status`);
                 
                 const savedAnswers = checklistStatusResponse.data.data;
                 if (savedAnswers && savedAnswers.length > 0) {
                   savedAnswers.forEach((answer: any) => {
                     const itemIndex = answer.item_index || 0;
                     if (completedMap.hasOwnProperty(itemIndex)) {
                       completedMap[itemIndex] = answer.completed || false;
                     }
                   });
                   setChecklistCompleted({...completedMap});
                 }
              } catch (error) {
                console.error('Failed to load checklist states:', error);
              }
              }
            }

      // Load deliverables
      if (taskData.deliverables) {
        setDeliverables(taskData.deliverables);
      }

      // Load comments
      if (taskData.messages) {
        setComments(taskData.messages);
      }
    } catch (error) {
      console.error('Failed to fetch task details:', error);
      toast.error('Failed to load task details');
    } finally {
      setLoading(false);
    }
  };

  const handleQuestionAnswerUpdate = async (questionId: number, answer: string) => {
    if (!task) return;
    
    try {
      await apiClient.post(`/tasks/${task.id}/question-answer`, {
        question_id: questionId,
        answer: answer,
      });
      
      // Update local state
      setQuestionAnswers(prev => 
        prev.map(qa => 
          qa.question_id === questionId 
            ? { ...qa, question_answer: answer }
            : qa
        )
      );
      
      toast.success('Answer updated successfully');
    } catch (error) {
      console.error('Failed to update answer:', error);
      toast.error('Failed to update answer');
    }
  };

  const handleChecklistToggle = async (index: number, completed: boolean) => {
    if (!task || !checklistId) return;
    
    try {
      
      // Get the current checklist item text
      const itemText = checklistItems[index] || `Checklist item ${index + 1}`;
      
      const requestData = {
        checklist_id: checklistId,
        item_index: index,
        completed: completed,
        notes: itemText,
      };
      
      const response = await apiClient.post(`/tasks/${task.id}/checklist-answer`, requestData);
      
      // Update local state immediately for better UX
      setChecklistCompleted(prev => ({
        ...prev,
        [index]: completed
      }));
      
      toast.success('Checklist updated successfully');
    } catch (error) {
      console.error('Failed to update checklist:', error);
      toast.error('Failed to update checklist');
    }
  };

  const handleDelete = async () => {
    if (!task || !confirm('Are you sure you want to delete this task?')) return;

    try {
      await apiClient.delete(`/tasks/${task.id}`);
      toast.success('Task deleted successfully');
      router.push('/tasks');
    } catch (error) {
      console.error('Failed to delete task:', error);
      toast.error('Failed to delete task');
    }
  };

  // Helper function to check if task is past due with strict deadline
  const isTaskPastDue = () => {
    if (!task) return false;
    if (!task.end_date || !task.close_deadline) return false;
    
    const deadline = new Date(task.end_date);
    const now = new Date();
    return now > deadline;
  };

  const handleAddComment = async () => {
    if (!comment.trim() || !task) return;

    // Check if task is past due
    if (isTaskPastDue()) {
      toast.error('Cannot add comments to a task that is past its strict deadline');
      return;
    }

    setSubmittingComment(true);
    try {
      await apiClient.post(`/tasks/${task.id}/messages`, {
        message_text: comment
      });
      toast.success('Comment added successfully');
      setComment('');
      // Refresh task to get new comments
      fetchTaskDetail(task.id.toString());
    } catch (error: any) {
      console.error('Failed to add comment:', error);
      if (error.response?.status === 403) {
        toast.error('Cannot add comments to a task that is past its strict deadline');
      } else {
        toast.error('Failed to add comment');
      }
    } finally {
      setSubmittingComment(false);
    }
  };

  const handleDeleteComment = async (commentId: string) => {
    if (!task || !confirm('Are you sure you want to delete this comment?')) return;

    try {
      await apiClient.delete(`/tasks/messages/${commentId}`);
      toast.success('Comment deleted successfully');
      fetchTaskDetail(task.id.toString());
    } catch (error) {
      console.error('Failed to delete comment:', error);
      toast.error('Failed to delete comment');
    }
  };

  const handleUpdateStatus = async (statusId: number) => {
    if (!task) return;

    setUpdatingStatus(true);
    try {
      await apiClient.put(`/tasks/${task.id}`, {
        status_id: statusId
      });
      toast.success('Status updated successfully');
      setEditingStatus(false);
      fetchTaskDetail(task.id.toString());
    } catch (error) {
      console.error('Failed to update status:', error);
      toast.error('Failed to update status');
    } finally {
      setUpdatingStatus(false);
    }
  };

  const handleUpdatePriority = async (priorityId: number) => {
    if (!task) return;

    setUpdatingPriority(true);
    try {
      await apiClient.put(`/tasks/${task.id}`, {
        priority_id: priorityId
      });
      toast.success('Priority updated successfully');
      setEditingPriority(false);
      fetchTaskDetail(task.id.toString());
    } catch (error) {
      console.error('Failed to update priority:', error);
      toast.error('Failed to update priority');
    } finally {
      setUpdatingPriority(false);
    }
  };

  const handleAddDeliverable = async () => {
    if (!deliverableForm.title.trim() || !task) return;

    // Check if task is past due
    if (isTaskPastDue()) {
      toast.error('Cannot add deliverables to a task that is past its strict deadline');
      return;
    }

    setSubmittingDeliverable(true);
    try {
      const formData = new FormData();
      formData.append('title', deliverableForm.title);
      formData.append('description', deliverableForm.description);
      formData.append('type', deliverableForm.type);
      if (deliverableForm.google_link) {
        formData.append('google_link', deliverableForm.google_link);
      }
      if (deliverableForm.external_link) {
        formData.append('external_link', deliverableForm.external_link);
      }
      
      // Add files
      deliverableFiles.forEach((file, index) => {
        formData.append(`files[${index}]`, file);
      });

      await apiClient.post(`/tasks/${task.id}/deliverables`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      
      toast.success('Deliverable added successfully');
      setShowDeliverableForm(false);
      setDeliverableForm({
        title: '',
        description: '',
        type: 'other',
        google_link: '',
        external_link: '',
      });
      setDeliverableFiles([]);
      // Refresh task to get updated deliverable info
      fetchTaskDetail(task.id.toString());
    } catch (error: any) {
      console.error('Failed to add deliverable:', error);
      if (error.response?.status === 403) {
        toast.error('Cannot add deliverables to a task that is past its strict deadline');
      } else {
        toast.error('Failed to add deliverable');
      }
    } finally {
      setSubmittingDeliverable(false);
    }
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files) {
      setDeliverableFiles(Array.from(e.target.files));
    }
  };

  const handleDeleteDeliverable = async (deliverableId: number) => {
    if (!task || !confirm('Are you sure you want to delete this deliverable?')) return;

    try {
      await apiClient.delete(`/tasks/${task.id}/deliverables/${deliverableId}`);
      toast.success('Deliverable deleted successfully');
      fetchTaskDetail(task.id.toString());
    } catch (error) {
      console.error('Failed to delete deliverable:', error);
      toast.error('Failed to delete deliverable');
    }
  };

  const handleViewDeliverable = (deliverable: any) => {
    setSelectedDeliverable(deliverable);
    setShowDeliverableModal(true);
  };

  const getStatusIcon = (statusName?: string) => {
    if (!statusName) return <Clock className="h-5 w-5 text-gray-500" />;
    
    switch (statusName.toLowerCase()) {
      case 'completed':
        return <CheckCircle className="h-5 w-5 text-green-500" />;
      case 'in progress':
        return <Clock className="h-5 w-5 text-blue-500" />;
      case 'pending':
        return <AlertCircle className="h-5 w-5 text-yellow-500" />;
      default:
        return <Clock className="h-5 w-5 text-gray-500" />;
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
            <p className="mt-2 text-muted-foreground">Loading task details...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  if (!task) {
    return (
      <MainLayout>
        <div className="text-center py-12">
          <p className="text-muted-foreground">Task not found</p>
          <Button onClick={() => router.push('/tasks')} className="mt-4">
            Back to Tasks
          </Button>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between flex-wrap gap-2">
          <div className="flex items-center gap-4">
            <Button
              variant="ghost"
              size="icon"
              onClick={() => router.push('/tasks')}
            >
              <ArrowLeft className="h-4 w-4" />
            </Button>
            <div>
              <h1 className="text-2xl font-bold">{task.title}</h1>
              <p className="text-sm text-muted-foreground">
                Created {formatDate(task.created_at)}
              </p>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              onClick={() => router.push(`/tasks/${task.id}/edit`)}
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

        {/* Past Due Warning */}
        {isTaskPastDue() && (
          <Card className="border-red-200 bg-red-50">
            <CardContent className="pt-6">
              <div className="flex items-center gap-2 text-red-700">
                <AlertTriangle className="h-5 w-5" />
                <div>
                  <p className="font-medium">Task Past Due</p>
                  <p className="text-sm">
                    This task has passed its strict deadline. Comments and deliverables cannot be added.
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        )}

        <div className="grid gap-6 lg:grid-cols-3">
          {/* Main Content */}
          <div className="lg:col-span-2 space-y-6">
            {/* Description */}
            <Card>
              <CardHeader>
                <CardTitle>Description</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-sm text-muted-foreground whitespace-pre-wrap break-words overflow-hidden overflow-y-auto max-h-96" style={{ wordBreak: 'break-word', overflowWrap: 'anywhere' }}>
                  <LinkifyContent>{task.description || 'No description provided'}</LinkifyContent>
                </div>
              </CardContent>
            </Card>

            {/* Brief */}
            {task && (
              <Card>
                <CardHeader>
                  <CardTitle>Brief</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="text-sm text-muted-foreground whitespace-pre-wrap break-words overflow-hidden overflow-y-auto max-h-96" style={{ wordBreak: 'break-word', overflowWrap: 'anywhere' }}>
                      <LinkifyContent>{task.standard_brief ?? task.template?.standard_brief ?? task.template_standard_brief ?? 'No brief provided'}</LinkifyContent>
                    </div>
                </CardContent>
              </Card>
            )}

            {/* Notes */}
            {task.note && (
              <Card>
                <CardHeader>
                  <CardTitle>Notes</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="text-sm text-muted-foreground whitespace-pre-wrap break-words overflow-hidden overflow-y-auto max-h-96" style={{ wordBreak: 'break-word', overflowWrap: 'anywhere' }}>
                    <LinkifyContent>{task.note}</LinkifyContent>
                  </div>
                </CardContent>
              </Card>
            )}

            {/* Task Questions */}
            {templateQuestions.length > 0 && (
              <Card>
                <CardHeader>
                  <CardTitle>Task Questions</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  {templateQuestions.map((question) => {
                    const answer = questionAnswers.find(a => a.question_id === question.id);
                    const currentAnswer = answer?.question_answer || '';
                    
                    return (
                      <div key={question.id} className="space-y-2">
                        <p className="font-medium text-sm">{question.question_text}</p>
                        <div className="space-y-2">
                          {question.question_type === 'select' && question.options ? (
                            <Select
                              value={currentAnswer}
                              onValueChange={(value) => handleQuestionAnswerUpdate(question.id, value)}
                            >
                              <SelectTrigger className="w-full">
                                <SelectValue placeholder="Select an option" />
                              </SelectTrigger>
                              <SelectContent>
                                {question.options.filter(option => option).map((option, index) => (
                                  <SelectItem key={index} value={option}>
                                    {option}
                                  </SelectItem>
                                ))}
                              </SelectContent>
                            </Select>
                          ) : question.question_type === 'checkbox' ? (
                            <div className="flex items-center space-x-2">
                              <Checkbox
                                checked={currentAnswer === 'true' || currentAnswer === '1'}
                                onCheckedChange={(checked) => 
                                  handleQuestionAnswerUpdate(question.id, checked ? 'true' : 'false')
                                }
                              />
                              <Label>Yes</Label>
                            </div>
                          ) : question.question_type === 'radio' && question.options ? (
                            <div className="space-y-2">
                              {question.options.filter(option => option).map((option, index) => (
                                <div key={index} className="flex items-center space-x-2">
                                  <input
                                    type="radio"
                                    id={`${question.id}-${index}`}
                                    name={`question-${question.id}`}
                                    value={option}
                                    checked={currentAnswer === option}
                                    onChange={(e) => handleQuestionAnswerUpdate(question.id, e.target.value)}
                                  />
                                  <Label htmlFor={`${question.id}-${index}`}>{option}</Label>
                                </div>
                              ))}
                            </div>
                          ) : question.question_type === 'textarea' ? (
                            <Textarea
                              placeholder="Enter your answer..."
                              value={currentAnswer}
                              onChange={(e) => handleQuestionAnswerUpdate(question.id, e.target.value)}
                              rows={3}
                            />
                          ) : (
                            <Input
                              placeholder="Enter your answer..."
                              value={currentAnswer}
                              onChange={(e) => handleQuestionAnswerUpdate(question.id, e.target.value)}
                            />
                          )}
                        </div>
                      </div>
                    );
                  })}
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
                      <Checkbox
                        checked={checklistCompleted[index] || false}
                        onCheckedChange={(checked) => {
                          handleChecklistToggle(index, checked as boolean);
                        }}
                      />
                      <span className={checklistCompleted[index] ? 'line-through text-muted-foreground' : ''}>
                        {item}
                      </span>
                    </div>
                  ))}
                </CardContent>
              </Card>
            )}
            




            {/* Comments */}
            <Card>
              <CardHeader>
                <CardTitle>Comments</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Textarea
                    placeholder={isTaskPastDue() ? "Cannot add comments to a task past its deadline" : "Add a comment..."}
                    value={comment}
                    onChange={(e) => setComment(e.target.value)}
                    rows={3}
                    disabled={isTaskPastDue()}
                  />
                  <Button
                    onClick={handleAddComment}
                    disabled={!comment.trim() || submittingComment || isTaskPastDue()}
                    size="sm"
                  >
                    <MessageSquare className="h-4 w-4 mr-2" />
                    {submittingComment ? 'Adding...' : isTaskPastDue() ? 'Comments Disabled' : 'Add Comment'}
                  </Button>
                </div>
                
                {/* Comments List */}
                <div className="space-y-4 mt-6">
                  {comments.length > 0 ? (
                    comments.map((comment) => (
                      <div key={comment.id} className="border rounded-lg p-4 space-y-2">
                        <div className="flex items-center justify-between">
                          <div className="flex items-center space-x-2">
                            <div className="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center">
                              <span className="text-sm font-medium text-primary">
                                {comment.sender?.first_name?.[0]}{comment.sender?.last_name?.[0]}
                              </span>
                            </div>
                            <div>
                              <p className="font-medium text-sm">
                                {comment.sender?.first_name} {comment.sender?.last_name}
                              </p>
                              <p className="text-xs text-muted-foreground">
                                {new Date(comment.created_at).toLocaleString()}
                              </p>
                            </div>
                          </div>
                          {(comment.sender?.id === user?.id || 
                            user?.roles?.some(role => ['admin', 'sub_admin', 'requester'].includes(role.name.toLowerCase()))) && (
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => handleDeleteComment(comment.id)}
                            >
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          )}
                        </div>
                        <p className="text-sm text-gray-700 ml-10 whitespace-pre-wrap break-words">
                          {comment.message_text}
                        </p>
                      </div>
                    ))
                  ) : (
                    <div className="text-center py-8">
                      <p className="text-sm text-muted-foreground">No comments yet</p>
                      <p className="text-xs text-muted-foreground mt-1">Be the first to add a comment!</p>
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Status & Priority */}
            <Card>
              <CardHeader>
                <CardTitle>Details</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <div className="flex items-center justify-between mb-2">
                    <p className="text-sm font-medium">Status</p>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => setEditingStatus(!editingStatus)}
                      disabled={updatingStatus}
                    >
                      <Edit3 className="h-3 w-3" />
                    </Button>
                  </div>
                  {editingStatus ? (
                    <div className="space-y-2">
                      <Select
                        value={task.status?.id?.toString() || ''}
                        onValueChange={(value) => handleUpdateStatus(parseInt(value))}
                        disabled={updatingStatus}
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent>
                          {statuses.map((status) => (
                            <SelectItem key={status.id} value={status.id.toString()}>
                              <div className="flex items-center gap-2">
                                                    {getStatusIcon(status.title)}
                    <span>{status.title}</span>
                              </div>
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                      <div className="flex gap-2">
                        <Button
                          size="sm"
                          onClick={() => setEditingStatus(false)}
                          variant="outline"
                        >
                          Cancel
                        </Button>
                      </div>
                    </div>
                  ) : (
                    <div className="flex items-center gap-2">
                      {getStatusIcon(task.status?.title)}
                      <Badge className="bg-green-100 text-green-800">
                        {task.status?.title || 'No Status'}
                      </Badge>
                    </div>
                  )}
                </div>

                <div>
                  <div className="flex items-center justify-between mb-2">
                    <p className="text-sm font-medium">Priority</p>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => setEditingPriority(!editingPriority)}
                      disabled={updatingPriority}
                    >
                      <Edit3 className="h-3 w-3" />
                    </Button>
                  </div>
                  {editingPriority ? (
                    <div className="space-y-2">
                      <Select
                        value={task.priority?.id?.toString() || ''}
                        onValueChange={(value) => handleUpdatePriority(parseInt(value))}
                        disabled={updatingPriority}
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Select priority" />
                        </SelectTrigger>
                        <SelectContent>
                          {priorities.map((priority) => (
                            <SelectItem key={priority.id} value={priority.id.toString()}>
                              <span>{priority.title}</span>
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                      <div className="flex gap-2">
                        <Button
                          size="sm"
                          onClick={() => setEditingPriority(false)}
                          variant="outline"
                        >
                          Cancel
                        </Button>
                      </div>
                    </div>
                  ) : (
                    <Badge className="bg-blue-100 text-blue-800">
                      {task.priority?.title || 'No Priority'}
                    </Badge>
                  )}
                </div>

                {task.project && (
                  <div>
                    <p className="text-sm font-medium mb-2">Project</p>
                    <p className="text-sm">{task.project.title}</p>
                  </div>
                )}

                {task.clients && task.clients.length > 0 && (
                  <div>
                    <p className="text-sm font-medium mb-2">Clients</p>
                    <div className="space-y-1">
                      {task.clients.map((client) => (
                        <div key={client.id} className="flex items-center gap-2">
                          <User className="h-3 w-3 text-muted-foreground" />
                          <p className="text-sm text-green-600">
                            {`${client.first_name || ''} ${client.last_name || ''}`.trim() || client.name || 'Unknown Client'}
                          </p>
                        </div>
                      ))}
                    </div>
                  </div>
                )}

                {task.deliverable_quantity && task.deliverable_quantity > 1 && (
                  <div>
                    <p className="text-sm font-medium mb-2">Quantity</p>
                    <p className="text-sm">{task.deliverable_quantity}x deliverables required</p>
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Dates */}
            <Card>
              <CardHeader>
                <CardTitle>Timeline</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="flex items-center gap-2 text-sm">
                  <Calendar className="h-4 w-4 text-muted-foreground" />
                  <div>
                    <p className="font-medium">Start Date</p>
                    <p className="text-muted-foreground">{formatDate(task.start_date)}</p>
                  </div>
                </div>
                <div className="flex items-center gap-2 text-sm">
                  <Calendar className="h-4 w-4 text-muted-foreground" />
                  <div>
                    <p className="font-medium">Due Date</p>
                    <p className="text-muted-foreground">{formatDate(task.end_date)}</p>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Tasker (Assigned To) */}
            {task.users && task.users.length > 0 && (
              <Card>
                <CardHeader>
                  <CardTitle>Tasker (Assigned To)</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-2">
                    {task.users.map((user) => (
                      <div key={user.id} className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                          <span className="text-xs font-medium text-blue-700">
                            {user.first_name?.[0]}{user.last_name?.[0]}
                          </span>
                        </div>
                        <div>
                          <p className="text-sm font-medium">
                            {user.first_name} {user.last_name}
                          </p>
                          <p className="text-xs text-muted-foreground">{user.email}</p>
                        </div>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            )}

            {/* Requester (Created By) */}
            {task.created_by && (
              <Card>
                <CardHeader>
                  <CardTitle>Requester (Created By)</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="flex items-center gap-3">
                    <div className="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                      <span className="text-xs font-medium text-green-700">
                        {task.created_by.first_name?.[0]}{task.created_by.last_name?.[0]}
                      </span>
                    </div>
                    <div>
                      <p className="text-sm font-medium">
                        {task.created_by.first_name} {task.created_by.last_name}
                      </p>
                      <p className="text-xs text-muted-foreground">{task.created_by.email}</p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            )}

            {/* Deliverables */}
            <Card>
              <CardHeader>
                <CardTitle>Deliverables</CardTitle>
                <CardDescription>Add deliverables for this task</CardDescription>
              </CardHeader>
              <CardContent className="space-y-3">
                {deliverables.length > 0 ? (
                  <div className="space-y-2">
                    {deliverables.map((deliverable) => (
                      <div key={deliverable.id} className="border rounded-lg p-2 hover:bg-gray-50 cursor-pointer">
                        <div className="flex items-center justify-between">
                          <div className="flex items-center space-x-2 flex-1 min-w-0">
                            <div className="flex-1 min-w-0">
                              <h4 className="font-medium text-sm truncate">{deliverable.title}</h4>
                              <div className="flex items-center gap-2 mt-1">
                                <Badge variant="secondary" className="text-xs">
                                  {deliverable.type}
                                </Badge>
                                {deliverable.media && deliverable.media.length > 0 && (
                                  <span className="text-xs text-muted-foreground">
                                    📎 {deliverable.media.length} file{deliverable.media.length > 1 ? 's' : ''}
                                  </span>
                                )}
                                {(deliverable.google_link || deliverable.external_link) && (
                                  <span className="text-xs text-muted-foreground">🔗</span>
                                )}
                              </div>
                            </div>
                          </div>
                          <div className="flex items-center gap-1">
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={(e) => {
                                e.stopPropagation();
                                handleViewDeliverable(deliverable);
                              }}
                              className="h-6 w-6 p-0"
                            >
                              <Eye className="h-3 w-3" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={(e) => {
                                e.stopPropagation();
                                handleDeleteDeliverable(deliverable.id);
                              }}
                              className="h-6 w-6 p-0"
                            >
                              <Trash2 className="h-3 w-3" />
                            </Button>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-4">
                    <p className="text-xs text-muted-foreground mb-3">No deliverables added yet</p>
                    <Button
                      onClick={() => setShowDeliverableForm(true)}
                      size="sm"
                      disabled={isTaskPastDue()}
                      className="w-full"
                    >
                      <Plus className="h-3 w-3 mr-1" />
                      {isTaskPastDue() ? 'Deliverables Disabled' : 'Add Deliverable'}
                    </Button>
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Tags */}
            {task.tags && task.tags.length > 0 && (
              <Card>
                <CardHeader>
                  <CardTitle>Tags</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="flex flex-wrap gap-2">
                    {task.tags.map((tag) => (
                      <Badge key={tag.id} variant="secondary">
                        <Tag className="h-3 w-3 mr-1" />
                        {tag.name}
                      </Badge>
                    ))}
                  </div>
                </CardContent>
              </Card>
            )}

            {/* Client Brief & Files */}
            <ClientBriefSection
              taskId={task.id}
            />
          </div>
        </div>
      </div>

      {/* Deliverable Form Modal */}
      {showDeliverableForm && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <h3 className="text-lg font-semibold mb-4">Add Deliverable</h3>
            <div className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="modal_deliverable_title">Title *</Label>
                <Input
                  id="modal_deliverable_title"
                  value={deliverableForm.title}
                  onChange={(e) => setDeliverableForm(prev => ({ ...prev, title: e.target.value }))}
                  placeholder="e.g., Website Design, Logo Design"
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="modal_deliverable_description">Description</Label>
                <Textarea
                  id="modal_deliverable_description"
                  value={deliverableForm.description}
                  onChange={(e) => setDeliverableForm(prev => ({ ...prev, description: e.target.value }))}
                  placeholder="Describe the deliverable..."
                  rows={3}
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="modal_deliverable_type">Type *</Label>
                <Select
                  value={deliverableForm.type}
                  onValueChange={(value) => setDeliverableForm(prev => ({ ...prev, type: value }))}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="design">Design</SelectItem>
                    <SelectItem value="document">Document</SelectItem>
                    <SelectItem value="presentation">Presentation</SelectItem>
                    <SelectItem value="file">File</SelectItem>
                    <SelectItem value="link">Link</SelectItem>
                    <SelectItem value="other">Other</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2">
                <Label htmlFor="google_link">Google Drive Link</Label>
                <Input
                  id="google_link"
                  type="url"
                  value={deliverableForm.google_link}
                  onChange={(e) => setDeliverableForm(prev => ({ ...prev, google_link: e.target.value }))}
                  placeholder="https://drive.google.com/..."
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="external_link">External Link</Label>
                <Input
                  id="external_link"
                  type="url"
                  value={deliverableForm.external_link}
                  onChange={(e) => setDeliverableForm(prev => ({ ...prev, external_link: e.target.value }))}
                  placeholder="https://..."
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="deliverable_files">Files</Label>
                <Input
                  id="deliverable_files"
                  type="file"
                  multiple
                  onChange={handleFileChange}
                  accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.txt"
                />
                <p className="text-xs text-muted-foreground">
                  Max 10MB per file. Supported: PDF, DOC, XLS, PPT, Images, TXT
                </p>
                {deliverableFiles.length > 0 && (
                  <div className="mt-2 space-y-1">
                    {deliverableFiles.map((file, index) => (
                      <div key={index} className="text-sm text-muted-foreground">
                        📎 {file.name} ({(file.size / 1024 / 1024).toFixed(2)} MB)
                      </div>
                    ))}
                  </div>
                )}
              </div>

              <div className="flex justify-end gap-2 pt-4">
                <Button
                  variant="outline"
                  onClick={() => {
                    setShowDeliverableForm(false);
                    setDeliverableForm({
                      title: '',
                      description: '',
                      type: 'other',
                      google_link: '',
                      external_link: '',
                    });
                    setDeliverableFiles([]);
                  }}
                  disabled={submittingDeliverable}
                >
                  Cancel
                </Button>
                <Button
                  onClick={handleAddDeliverable}
                  disabled={!deliverableForm.title.trim() || submittingDeliverable}
                >
                  {submittingDeliverable ? 'Adding...' : 'Add Deliverable'}
                </Button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Deliverable View Modal */}
      {showDeliverableModal && selectedDeliverable && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-xl font-bold">{selectedDeliverable.title}</h2>
              <Button
                variant="ghost"
                size="sm"
                onClick={() => {
                  setShowDeliverableModal(false);
                  setSelectedDeliverable(null);
                }}
              >
                <X className="h-4 w-4" />
              </Button>
            </div>

            <div className="space-y-4">
              {/* Type Badge */}
              <div className="flex items-center gap-2">
                <Badge variant="secondary">
                  {selectedDeliverable.type}
                </Badge>
                {selectedDeliverable.completed_at && (
                  <Badge variant="default" className="bg-green-100 text-green-800">
                    Completed
                  </Badge>
                )}
              </div>

              {/* Description */}
              {selectedDeliverable.description && (
                <div>
                  <h3 className="font-medium mb-2">Description</h3>
                  <p className="text-sm text-muted-foreground whitespace-pre-wrap">
                    {selectedDeliverable.description}
                  </p>
                </div>
              )}

              {/* Links */}
              {(selectedDeliverable.google_link || selectedDeliverable.external_link) && (
                <div>
                  <h3 className="font-medium mb-2">Links</h3>
                  <div className="space-y-2">
                    {selectedDeliverable.google_link && (
                      <a
                        href={selectedDeliverable.google_link}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex items-center gap-2 text-blue-600 hover:text-blue-800 text-sm"
                      >
                        <span>🔗</span>
                        <span>Google Drive</span>
                      </a>
                    )}
                    {selectedDeliverable.external_link && (
                      <a
                        href={selectedDeliverable.external_link}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex items-center gap-2 text-blue-600 hover:text-blue-800 text-sm"
                      >
                        <span>🔗</span>
                        <span>External Link</span>
                      </a>
                    )}
                  </div>
                </div>
              )}

              {/* Files */}
              {selectedDeliverable.media && selectedDeliverable.media.length > 0 && (
                <div>
                  <h3 className="font-medium mb-2">Files</h3>
                  <div className="space-y-2">
                    {selectedDeliverable.media.map((file: any) => (
                      <a
                        key={file.id}
                        href={getFileUrl(file.original_url)}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex items-center gap-2 text-blue-600 hover:text-blue-800 text-sm"
                      >
                        <span>📎</span>
                        <span>{file.file_name}</span>
                      </a>
                    ))}
                  </div>
                </div>
              )}

              {/* Completion Date */}
              {selectedDeliverable.completed_at && (
                <div>
                  <h3 className="font-medium mb-2">Completed</h3>
                  <p className="text-sm text-muted-foreground">
                    {new Date(selectedDeliverable.completed_at).toLocaleDateString()}
                  </p>
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </MainLayout>
  );
}
