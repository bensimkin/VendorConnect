'use client';

import { useState, useEffect } from 'react';
import { useParams, useRouter } from 'next/navigation';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import apiClient from '@/lib/api-client';
import { ArrowLeft, Edit, Trash2, Calendar, User, Tag, MessageSquare, Paperclip, Clock, CheckCircle, AlertCircle } from 'lucide-react';
import { format } from 'date-fns';
import { toast } from 'react-hot-toast';

interface TaskDetail {
  id: number;
  title: string;
  description?: string;
  status?: {
    id: number;
    name: string;
    color?: string;
  };
  priority?: {
    id: number;
    name: string;
    color?: string;
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
  }>;
  project?: {
    id: number;
    name: string;
  };
  start_date?: string;
  end_date?: string;
  created_at: string;
  updated_at: string;
  tags?: Array<{ id: number; name: string }>;
  note?: string;
  template?: {
    id: number;
    template_name: string;
  };
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

export default function TaskDetailPage() {
  const params = useParams();
  const router = useRouter();
  const [task, setTask] = useState<TaskDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [comment, setComment] = useState('');
  const [submittingComment, setSubmittingComment] = useState(false);
  const [templateQuestions, setTemplateQuestions] = useState<TemplateQuestion[]>([]);
  const [questionAnswers, setQuestionAnswers] = useState<QuestionAnswer[]>([]);
  const [checklistItems, setChecklistItems] = useState<string[]>([]);
  const [checklistCompleted, setChecklistCompleted] = useState<Record<number, boolean>>({});

  useEffect(() => {
    if (params.id) {
      fetchTaskDetail(params.id as string);
    }
  }, [params.id]);

  const fetchTaskDetail = async (id: string) => {
    try {
      const response = await apiClient.get(`/tasks/${id}`);
      const taskData = response.data.data;
      setTask(taskData);

      // Load template questions and checklists from task data
      if (taskData.question_answers && taskData.question_answers.length > 0) {
        const questions: TemplateQuestion[] = [];
        const answers: QuestionAnswer[] = [];
        
        taskData.question_answers.forEach((qa: any) => {
          if (qa.brief_questions) {
            questions.push({
              id: qa.brief_questions.id,
              question_text: qa.brief_questions.question_text,
              question_type: qa.brief_questions.question_type,
              options: qa.brief_questions.options,
            });
            answers.push({
              id: qa.id,
              question_id: qa.question_id,
              question_answer: qa.question_answer,
              briefQuestions: {
                id: qa.brief_questions.id,
                question_text: qa.brief_questions.question_text,
                question_type: qa.brief_questions.question_type,
                options: qa.brief_questions.options,
              },
            });
          }
        });
        
        setTemplateQuestions(questions);
        setQuestionAnswers(answers);
      }

      // Load checklist from task data
      if (taskData.checklist_answers && taskData.checklist_answers.length > 0) {
        const checklistItems: string[] = [];
        const completedMap: Record<number, boolean> = {};
        
        taskData.checklist_answers.forEach((ca: any, index: number) => {
          if (ca.notes) {
            checklistItems.push(ca.notes);
            completedMap[index] = ca.completed || false;
          }
        });
        
        setChecklistItems(checklistItems);
        setChecklistCompleted(completedMap);
      }
    } catch (error) {
      console.error('Failed to fetch task details:', error);
      toast.error('Failed to load task details');
    } finally {
      setLoading(false);
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

  const handleAddComment = async () => {
    if (!comment.trim() || !task) return;

    setSubmittingComment(true);
    try {
      await apiClient.post(`/tasks/${task.id}/messages`, {
        message: comment
      });
      toast.success('Comment added successfully');
      setComment('');
      // Refresh task to get new comments
      fetchTaskDetail(task.id.toString());
    } catch (error) {
      console.error('Failed to add comment:', error);
      toast.error('Failed to add comment');
    } finally {
      setSubmittingComment(false);
    }
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
        <div className="flex items-center justify-between">
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

        <div className="grid gap-6 lg:grid-cols-3">
          {/* Main Content */}
          <div className="lg:col-span-2 space-y-6">
            {/* Description */}
            <Card>
              <CardHeader>
                <CardTitle>Description</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-sm text-muted-foreground whitespace-pre-wrap">
                  {task.description || 'No description provided'}
                </p>
              </CardContent>
            </Card>

            {/* Notes */}
            {task.note && (
              <Card>
                <CardHeader>
                  <CardTitle>Notes</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-sm text-muted-foreground whitespace-pre-wrap">
                    {task.note}
                  </p>
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
                  {templateQuestions.map((question) => {
                    const answer = questionAnswers.find(a => a.question_id === question.id);
                    return (
                      <div key={question.id} className="space-y-2">
                        <p className="font-medium text-sm">{question.question_text}</p>
                        <div className="text-sm text-muted-foreground">
                          {answer ? (
                            <span>{answer.question_answer}</span>
                          ) : (
                            <span className="italic">No answer provided</span>
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
                      <input
                        type="checkbox"
                        checked={checklistCompleted[index] || false}
                        disabled
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

            {/* Comments */}
            <Card>
              <CardHeader>
                <CardTitle>Comments</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Textarea
                    placeholder="Add a comment..."
                    value={comment}
                    onChange={(e) => setComment(e.target.value)}
                    rows={3}
                  />
                  <Button
                    onClick={handleAddComment}
                    disabled={!comment.trim() || submittingComment}
                    size="sm"
                  >
                    <MessageSquare className="h-4 w-4 mr-2" />
                    Add Comment
                  </Button>
                </div>
                <div className="text-sm text-muted-foreground text-center py-4">
                  Comments feature will be fully implemented with the messaging system
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
                  <p className="text-sm font-medium mb-2">Status</p>
                  <div className="flex items-center gap-2">
                    {getStatusIcon(task.status?.name)}
                    <Badge
                      style={{
                        backgroundColor: task.status?.color ? `${task.status.color}20` : undefined,
                        color: task.status?.color || undefined,
                      }}
                    >
                      {task.status?.name || 'No Status'}
                    </Badge>
                  </div>
                </div>

                <div>
                  <p className="text-sm font-medium mb-2">Priority</p>
                  <Badge
                    style={{
                      backgroundColor: task.priority?.color ? `${task.priority.color}20` : undefined,
                      color: task.priority?.color || undefined,
                    }}
                  >
                    {task.priority?.name || 'No Priority'}
                  </Badge>
                </div>

                {task.project && (
                  <div>
                    <p className="text-sm font-medium mb-2">Project</p>
                    <p className="text-sm">{task.project.name}</p>
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

            {/* Assigned Users */}
            {task.users && task.users.length > 0 && (
              <Card>
                <CardHeader>
                  <CardTitle>Assigned To</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-2">
                    {task.users.map((user) => (
                      <div key={user.id} className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                          <span className="text-xs font-medium">
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
          </div>
        </div>
      </div>
    </MainLayout>
  );
}
