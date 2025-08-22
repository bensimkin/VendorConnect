'use client';

import { useState, useEffect } from 'react';
import { useRouter, useParams } from 'next/navigation';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import apiClient from '@/lib/api-client';
import { toast } from 'react-hot-toast';
import { ArrowLeft, Save, FileText, Plus, Trash2, GripVertical } from 'lucide-react';

interface TaskType {
  id: number;
  task_type: string;
}

interface Template {
  id: number;
  template_name: string;
  task_type_id: number;
  task_type?: TaskType;
}

interface Question {
  id?: number;
  task_brief_templates_id?: number;
  question_text: string;
  question_type: 'text' | 'textarea' | 'select' | 'checkbox' | 'radio';
  order?: number;
}

interface ChecklistItem {
  id?: number;
  task_brief_templates_id?: number;
  checklist: string | string[];
  order?: number;
}

export default function EditTemplatePage() {
  const router = useRouter();
  const params = useParams();
  const templateId = params.id as string;
  
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [taskTypes, setTaskTypes] = useState<TaskType[]>([]);
  const [template, setTemplate] = useState<Template | null>(null);
  const [formData, setFormData] = useState({
    template_name: '',
    task_type_id: '',
  });
  
  const [questions, setQuestions] = useState<Question[]>([]);
  const [checklistItems, setChecklistItems] = useState<string[]>([]);
  const [savingQuestions, setSavingQuestions] = useState(false);
  const [savingChecklist, setSavingChecklist] = useState(false);

  useEffect(() => {
    fetchData();
  }, [templateId]);

  const fetchData = async () => {
    try {
      const [templateRes, taskTypesRes, questionsRes, checklistRes] = await Promise.all([
        apiClient.get(`/task-brief-templates/${templateId}`),
        apiClient.get('/task-types'),
        apiClient.get(`/task-brief-questions?template_id=${templateId}`),
        apiClient.get(`/task-brief-checklists?template_id=${templateId}`),
      ]);

      const templateData = templateRes.data.data;
      setTemplate(templateData);
      setFormData({
        template_name: templateData.template_name || '',
        task_type_id: templateData.task_type_id?.toString() || '',
      });

      const types = taskTypesRes.data.data || [];
      setTaskTypes(types);
      
      // Load existing questions
      const questionsData = questionsRes.data.data?.data || questionsRes.data.data || [];
      setQuestions(questionsData);
      
      // Load existing checklist
      const checklistData = checklistRes.data.data?.data || checklistRes.data.data || [];
      if (checklistData.length > 0 && checklistData[0].checklist) {
        // Handle both array and string formats
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
      
    } catch (error) {
      console.error('Failed to fetch template data:', error);
      toast.error('Failed to load template');
      router.push('/templates');
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.template_name.trim()) {
      toast.error('Template name is required');
      return;
    }

    if (!formData.task_type_id) {
      toast.error('Task type is required');
      return;
    }

    setSaving(true);
    try {
      const payload = {
        template_name: formData.template_name.trim(),
        task_type_id: parseInt(formData.task_type_id),
      };

      await apiClient.put(`/task-brief-templates/${templateId}`, payload);
      toast.success('Template updated successfully');
    } catch (error: any) {
      console.error('Failed to update template:', error);
      toast.error(error.response?.data?.message || 'Failed to update template');
    } finally {
      setSaving(false);
    }
  };

  const addQuestion = () => {
    setQuestions([
      ...questions,
      {
        question_text: '',
        question_type: 'text',
        task_brief_templates_id: parseInt(templateId),
      },
    ]);
  };

  const updateQuestion = (index: number, updates: Partial<Question>) => {
    setQuestions(questions.map((q, i) => 
      i === index ? { ...q, ...updates } : q
    ));
  };

  const removeQuestion = async (index: number) => {
    const question = questions[index];
    if (question.id) {
      try {
        await apiClient.delete(`/task-brief-questions/${question.id}`);
        toast.success('Question deleted successfully');
      } catch (error) {
        toast.error('Failed to delete question');
        return;
      }
    }
    setQuestions(questions.filter((_, i) => i !== index));
  };

  const saveQuestions = async () => {
    setSavingQuestions(true);
    try {
      for (const question of questions) {
        if (!question.question_text.trim()) continue;
        
        const payload = {
          template_id: parseInt(templateId),
          question: question.question_text,
          question_type: question.question_type,
        };

        if (question.id) {
          await apiClient.put(`/task-brief-questions/${question.id}`, payload);
        } else {
          await apiClient.post('/task-brief-questions', payload);
        }
      }
      toast.success('Questions saved successfully');
      fetchData(); // Reload to get updated IDs
    } catch (error: any) {
      console.error('Failed to save questions:', error);
      toast.error('Failed to save questions');
    } finally {
      setSavingQuestions(false);
    }
  };

  const addChecklistItem = () => {
    setChecklistItems([...checklistItems, '']);
  };

  const updateChecklistItem = (index: number, value: string) => {
    setChecklistItems(checklistItems.map((item, i) => 
      i === index ? value : item
    ));
  };

  const removeChecklistItem = (index: number) => {
    setChecklistItems(checklistItems.filter((_, i) => i !== index));
  };

  const saveChecklist = async () => {
    setSavingChecklist(true);
    try {
      const filteredItems = checklistItems.filter(item => item.trim());
      
      // Delete existing checklist items
      const existingRes = await apiClient.get(`/task-brief-checklists?template_id=${templateId}`);
      const existingItems = existingRes.data.data?.data || existingRes.data.data || [];
      
      for (const item of existingItems) {
        await apiClient.delete(`/task-brief-checklists/${item.id}`);
      }
      
      // Create new checklist items
      for (let i = 0; i < filteredItems.length; i++) {
        const payload = {
          template_id: parseInt(templateId),
          item: filteredItems[i],
          order: i,
        };
        await apiClient.post('/task-brief-checklists', payload);
      }
      
      toast.success('Checklist saved successfully');
      fetchData(); // Reload
    } catch (error: any) {
      console.error('Failed to save checklist:', error);
      toast.error('Failed to save checklist');
    } finally {
      setSavingChecklist(false);
    }
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading template...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="max-w-4xl mx-auto space-y-6">
        {/* Header */}
        <div className="flex items-center gap-4">
          <Button
            variant="ghost"
            size="icon"
            onClick={() => router.push('/templates')}
          >
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div>
            <h1 className="text-2xl font-bold">Edit Template</h1>
            <p className="text-sm text-muted-foreground">Modify your task template</p>
          </div>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit} className="space-y-6">
          <Card>
            <CardHeader>
              <div className="flex items-center gap-2">
                <FileText className="h-5 w-5" />
                <CardTitle>Template Details</CardTitle>
              </div>
              <CardDescription>Basic template information</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="template_name">Template Name *</Label>
                <Input
                  id="template_name"
                  value={formData.template_name}
                  onChange={(e) => setFormData({ ...formData, template_name: e.target.value })}
                  placeholder="e.g., Website Development, Bug Fix, Feature Request"
                  required
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="task_type">Task Type *</Label>
                <select
                  id="task_type"
                  value={formData.task_type_id}
                  onChange={(e) => setFormData({ ...formData, task_type_id: e.target.value })}
                  className="w-full px-3 py-2 border rounded-md bg-background"
                  required
                >
                  <option value="">Select a task type</option>
                  {taskTypes.map((type) => (
                    <option key={type.id} value={type.id}>
                      {type.task_type}
                    </option>
                  ))}
                </select>
              </div>

              <Button type="submit" disabled={saving}>
                {saving ? 'Saving...' : 'Save Template Details'}
              </Button>
            </CardContent>
          </Card>
        </form>

        {/* Questions Section */}
        <Card>
          <CardHeader>
            <CardTitle>Template Questions</CardTitle>
            <CardDescription>
              Questions to ask when creating a task from this template
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            {questions.length > 0 ? (
              <div className="space-y-3">
                {questions.map((question, index) => (
                  <div key={index} className="flex gap-2 items-start p-3 border rounded-lg">
                    <GripVertical className="h-5 w-5 text-muted-foreground mt-2 cursor-move" />
                    <div className="flex-1 space-y-2">
                      <Input
                        placeholder="Question text"
                        value={question.question_text}
                        onChange={(e) => updateQuestion(index, { question_text: e.target.value })}
                      />
                      <div className="flex gap-2">
                        <select
                          value={question.question_type}
                          onChange={(e) => updateQuestion(index, { question_type: e.target.value as Question['question_type'] })}
                          className="px-2 py-1 border rounded text-sm"
                        >
                          <option value="text">Short Text</option>
                          <option value="textarea">Long Text</option>
                          <option value="select">Dropdown</option>
                          <option value="checkbox">Checkbox</option>
                          <option value="radio">Radio Buttons</option>
                        </select>
                      </div>
                    </div>
                    <Button
                      type="button"
                      size="sm"
                      variant="ghost"
                      onClick={() => removeQuestion(index)}
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-sm text-muted-foreground text-center py-4">
                No questions added yet
              </p>
            )}
            
            <div className="flex gap-2">
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={addQuestion}
              >
                <Plus className="h-4 w-4 mr-2" />
                Add Question
              </Button>
              {questions.length > 0 && (
                <Button
                  type="button"
                  size="sm"
                  onClick={saveQuestions}
                  disabled={savingQuestions}
                >
                  <Save className="h-4 w-4 mr-2" />
                  {savingQuestions ? 'Saving...' : 'Save Questions'}
                </Button>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Checklist Section */}
        <Card>
          <CardHeader>
            <CardTitle>Template Checklist</CardTitle>
            <CardDescription>
              Default checklist items for tasks created from this template
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            {checklistItems.length > 0 ? (
              <div className="space-y-2">
                {checklistItems.map((item, index) => (
                  <div key={index} className="flex gap-2 items-center">
                    <GripVertical className="h-5 w-5 text-muted-foreground cursor-move" />
                    <Input
                      placeholder="Checklist item"
                      value={item}
                      onChange={(e) => updateChecklistItem(index, e.target.value)}
                      className="flex-1"
                    />
                    <Button
                      type="button"
                      size="sm"
                      variant="ghost"
                      onClick={() => removeChecklistItem(index)}
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-sm text-muted-foreground text-center py-4">
                No checklist items added yet
              </p>
            )}
            
            <div className="flex gap-2">
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={addChecklistItem}
              >
                <Plus className="h-4 w-4 mr-2" />
                Add Checklist Item
              </Button>
              {checklistItems.length > 0 && (
                <Button
                  type="button"
                  size="sm"
                  onClick={saveChecklist}
                  disabled={savingChecklist}
                >
                  <Save className="h-4 w-4 mr-2" />
                  {savingChecklist ? 'Saving...' : 'Save Checklist'}
                </Button>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}