'use client';

import { useState, useEffect } from 'react';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import apiClient from '@/lib/api-client';
import { Plus, Search, Tag, Edit, Trash2, Save, X } from 'lucide-react';
import { toast } from 'react-hot-toast';

interface TaskType {
  id: number;
  name: string;
  created_at: string;
  updated_at: string;
}

export default function TaskTypesPage() {
  const [taskTypes, setTaskTypes] = useState<TaskType[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [showNewForm, setShowNewForm] = useState(false);
  const [newTypeName, setNewTypeName] = useState('');
  const [editingId, setEditingId] = useState<number | null>(null);
  const [editingName, setEditingName] = useState('');
  const [savingId, setSavingId] = useState<number | null>(null);

  useEffect(() => {
    fetchTaskTypes();
  }, []);

  const fetchTaskTypes = async () => {
    try {
      const response = await apiClient.get('/task-types');
      const typesData = response.data.data || [];
      setTaskTypes(Array.isArray(typesData) ? typesData : []);
    } catch (error) {
      console.error('Failed to fetch task types:', error);
      toast.error('Failed to load task types');
      setTaskTypes([]);
    } finally {
      setLoading(false);
    }
  };

  const handleCreate = async () => {
    if (!newTypeName.trim()) {
      toast.error('Task type name is required');
      return;
    }

    try {
      const response = await apiClient.post('/task-types', {
        name: newTypeName.trim(),
      });
      toast.success('Task type created successfully');
      setTaskTypes([...taskTypes, response.data.data]);
      setNewTypeName('');
      setShowNewForm(false);
    } catch (error: any) {
      console.error('Failed to create task type:', error);
      toast.error(error.response?.data?.message || 'Failed to create task type');
    }
  };

  const handleUpdate = async (id: number) => {
    if (!editingName.trim()) {
      toast.error('Task type name is required');
      return;
    }

    setSavingId(id);
    try {
      await apiClient.put(`/task-types/${id}`, {
        name: editingName.trim(),
      });
      toast.success('Task type updated successfully');
      setTaskTypes(taskTypes.map(type => 
        type.id === id ? { ...type, name: editingName.trim() } : type
      ));
      setEditingId(null);
      setEditingName('');
    } catch (error: any) {
      console.error('Failed to update task type:', error);
      toast.error(error.response?.data?.message || 'Failed to update task type');
    } finally {
      setSavingId(null);
    }
  };

  const handleDelete = async (id: number, name: string) => {
    if (!confirm(`Are you sure you want to delete the task type "${name}"? This action cannot be undone.`)) {
      return;
    }

    try {
      await apiClient.delete(`/task-types/${id}`);
      toast.success('Task type deleted successfully');
      setTaskTypes(taskTypes.filter(type => type.id !== id));
    } catch (error: any) {
      console.error('Failed to delete task type:', error);
      toast.error(error.response?.data?.message || 'Failed to delete task type. It may be in use by templates.');
    }
  };

  const startEditing = (type: TaskType) => {
    setEditingId(type.id);
    setEditingName(type.name);
  };

  const cancelEditing = () => {
    setEditingId(null);
    setEditingName('');
  };

  const filteredTypes = taskTypes.filter(type =>
    (type.name?.toLowerCase() || '').includes(searchTerm.toLowerCase())
  );

  if (loading) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading task types...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="max-w-4xl mx-auto space-y-8">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Task Types</h1>
            <p className="text-muted-foreground">Manage the types of tasks available in your system</p>
          </div>
          <Button 
            onClick={() => setShowNewForm(true)}
            disabled={showNewForm}
          >
            <Plus className="mr-2 h-4 w-4" />
            New Task Type
          </Button>
        </div>

        {/* Search */}
        <Card>
          <CardContent className="p-4">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
              <Input
                placeholder="Search task types..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10"
              />
            </div>
          </CardContent>
        </Card>

        {/* New Task Type Form */}
        {showNewForm && (
          <Card className="border-primary">
            <CardHeader>
              <CardTitle className="text-lg">Create New Task Type</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex gap-2">
                <Input
                  placeholder="Enter task type name"
                  value={newTypeName}
                  onChange={(e) => setNewTypeName(e.target.value)}
                  onKeyPress={(e) => e.key === 'Enter' && handleCreate()}
                  autoFocus
                />
                <Button onClick={handleCreate} size="sm">
                  <Save className="h-4 w-4 mr-1" />
                  Save
                </Button>
                <Button 
                  onClick={() => {
                    setShowNewForm(false);
                    setNewTypeName('');
                  }}
                  size="sm"
                  variant="outline"
                >
                  <X className="h-4 w-4 mr-1" />
                  Cancel
                </Button>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Task Types List */}
        <Card>
          <CardHeader>
            <CardTitle>Existing Task Types</CardTitle>
            <CardDescription>
              {filteredTypes.length} task type{filteredTypes.length !== 1 ? 's' : ''} found
            </CardDescription>
          </CardHeader>
          <CardContent>
            {filteredTypes.length > 0 ? (
              <div className="space-y-2">
                {filteredTypes.map((type) => (
                  <div
                    key={type.id}
                    className="flex items-center justify-between p-3 rounded-lg border hover:bg-accent/50 transition-colors"
                  >
                    {editingId === type.id ? (
                      <>
                        <div className="flex items-center gap-2 flex-1">
                          <Tag className="h-4 w-4 text-muted-foreground" />
                          <Input
                            value={editingName}
                            onChange={(e) => setEditingName(e.target.value)}
                            onKeyPress={(e) => {
                              if (e.key === 'Enter') handleUpdate(type.id);
                              if (e.key === 'Escape') cancelEditing();
                            }}
                            className="flex-1"
                            autoFocus
                          />
                        </div>
                        <div className="flex items-center gap-2 ml-4">
                          <Button
                            size="sm"
                            onClick={() => handleUpdate(type.id)}
                            disabled={savingId === type.id}
                          >
                            {savingId === type.id ? 'Saving...' : 'Save'}
                          </Button>
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={cancelEditing}
                            disabled={savingId === type.id}
                          >
                            Cancel
                          </Button>
                        </div>
                      </>
                    ) : (
                      <>
                        <div className="flex items-center gap-2">
                          <Tag className="h-4 w-4 text-muted-foreground" />
                          <span className="font-medium">{type.name}</span>
                        </div>
                        <div className="flex items-center gap-2">
                          <Button
                            size="sm"
                            variant="ghost"
                            onClick={() => startEditing(type)}
                          >
                            <Edit className="h-4 w-4" />
                          </Button>
                          <Button
                            size="sm"
                            variant="ghost"
                            className="text-destructive hover:text-destructive"
                            onClick={() => handleDelete(type.id, type.name)}
                          >
                            <Trash2 className="h-4 w-4" />
                          </Button>
                        </div>
                      </>
                    )}
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-8">
                <Tag className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                <p className="text-muted-foreground">
                  {searchTerm ? 'No task types found matching your search.' : 'No task types created yet.'}
                </p>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Info Card */}
        <Card className="bg-muted/50">
          <CardContent className="pt-6">
            <h4 className="font-medium text-sm mb-2">ℹ️ About Task Types</h4>
            <p className="text-sm text-muted-foreground">
              Task types help categorize your tasks and are used by templates to organize different 
              kinds of work. Common examples include "Bug", "Feature", "Support", "Documentation", etc.
              Templates are associated with specific task types.
            </p>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}
