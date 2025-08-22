'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import apiClient from '@/lib/api-client';
import { toast } from 'react-hot-toast';
import { ArrowLeft, Save, FileText } from 'lucide-react';

interface TaskType {
  id: number;
  name: string;
}

export default function NewTemplatePage() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [taskTypes, setTaskTypes] = useState<TaskType[]>([]);
  const [formData, setFormData] = useState({
    template_name: '',
    task_type_id: '',
  });

  useEffect(() => {
    fetchTaskTypes();
  }, []);

  const fetchTaskTypes = async () => {
    try {
      const response = await apiClient.get('/task-types');
      const types = response.data.data || [];
      setTaskTypes(types);
      
      // Set default value
      if (types.length > 0) {
        setFormData(prev => ({ ...prev, task_type_id: types[0].id.toString() }));
      }
    } catch (error) {
      console.error('Failed to fetch task types:', error);
      toast.error('Failed to load task types');
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

    setLoading(true);
    try {
      const payload = {
        template_name: formData.template_name.trim(),
        task_type_id: parseInt(formData.task_type_id),
      };

      await apiClient.post('/task-brief-templates', payload);
      toast.success('Template created successfully');
      router.push('/templates');
    } catch (error: any) {
      console.error('Failed to create template:', error);
      toast.error(error.response?.data?.message || 'Failed to create template');
    } finally {
      setLoading(false);
    }
  };

  return (
    <MainLayout>
      <div className="max-w-2xl mx-auto space-y-6">
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
            <h1 className="text-2xl font-bold">Create Template</h1>
            <p className="text-sm text-muted-foreground">Create a reusable task template</p>
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
              <CardDescription>Define the basic template information</CardDescription>
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
                <p className="text-sm text-muted-foreground">
                  Give your template a descriptive name
                </p>
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
                      {type.name}
                    </option>
                  ))}
                </select>
                <p className="text-sm text-muted-foreground">
                  Choose the type of task this template is for
                </p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Questions & Checklists</CardTitle>
              <CardDescription>These can be configured after creating the template</CardDescription>
            </CardHeader>
            <CardContent>
              <p className="text-sm text-muted-foreground">
                After creating this template, you'll be able to add:
              </p>
              <ul className="list-disc list-inside text-sm text-muted-foreground mt-2 space-y-1">
                <li>Custom questions for task briefs</li>
                <li>Checklist items for task completion</li>
                <li>Default values and requirements</li>
              </ul>
            </CardContent>
          </Card>

          {/* Actions */}
          <div className="flex justify-end gap-4">
            <Button
              type="button"
              variant="outline"
              onClick={() => router.push('/templates')}
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
                  Create Template
                </>
              )}
            </Button>
          </div>
        </form>

        {/* Info Card */}
        <Card className="bg-muted/50">
          <CardContent className="pt-6">
            <h4 className="font-medium text-sm mb-2">💡 Pro Tip</h4>
            <p className="text-sm text-muted-foreground">
              Templates help standardize your workflow. Create templates for common task types
              like "Bug Report", "Feature Request", or "Client Feedback" to ensure consistency
              and save time when creating new tasks.
            </p>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}
