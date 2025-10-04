'use client';

import { useState, useEffect } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { toast } from 'react-hot-toast';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Switch } from '@/components/ui/switch';
import apiClient from '@/lib/api-client';
import { ArrowLeft, Plus, Eye, EyeOff, Edit, Trash2, ExternalLink, Copy, Check } from 'lucide-react';

interface Credential {
  id: number;
  title: string;
  url?: string;
  username?: string;
  notes?: string;
  is_active: boolean;
  created_at: string;
}

interface Client {
  id: number;
  name: string;
}

export default function ClientCredentialsPage() {
  const router = useRouter();
  const params = useParams();
  const clientId = params.id as string;

  const [client, setClient] = useState<Client | null>(null);
  const [credentials, setCredentials] = useState<Credential[]>([]);
  const [loading, setLoading] = useState(true);
  const [showPassword, setShowPassword] = useState<{ [key: number]: boolean }>({});
  const [passwords, setPasswords] = useState<{ [key: number]: string }>({});
  const [copied, setCopied] = useState<{ [key: number]: boolean }>({});
  const [isAddDialogOpen, setIsAddDialogOpen] = useState(false);
  const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
  const [editingCredential, setEditingCredential] = useState<Credential | null>(null);
  const [formData, setFormData] = useState({
    title: '',
    url: '',
    username: '',
    password: '',
    notes: '',
    is_active: true,
  });

  useEffect(() => {
    if (clientId) {
      fetchClient();
      fetchCredentials();
    }
  }, [clientId]);

  const fetchClient = async () => {
    try {
      const response = await apiClient.get(`/clients/${clientId}`);
      setClient(response.data.data);
    } catch (error) {
      console.error('Failed to fetch client:', error);
      toast.error('Failed to load client details');
      router.push('/clients');
    }
  };

  const fetchCredentials = async () => {
    try {
      const response = await apiClient.get(`/clients/${clientId}/credentials`);
      setCredentials(response.data.data);
    } catch (error) {
      console.error('Failed to fetch credentials:', error);
      toast.error('Failed to load credentials');
    } finally {
      setLoading(false);
    }
  };

  const handleAddCredential = async () => {
    try {
      await apiClient.post(`/clients/${clientId}/credentials`, formData);
      toast.success('Credential added successfully');
      setIsAddDialogOpen(false);
      resetForm();
      fetchCredentials();
    } catch (error: any) {
      console.error('Failed to add credential:', error);
      toast.error(error.response?.data?.message || 'Failed to add credential');
    }
  };

  const handleEditCredential = async () => {
    if (!editingCredential) return;
    
    try {
      await apiClient.put(`/clients/${clientId}/credentials/${editingCredential.id}`, formData);
      toast.success('Credential updated successfully');
      setIsEditDialogOpen(false);
      setEditingCredential(null);
      resetForm();
      fetchCredentials();
    } catch (error: any) {
      console.error('Failed to update credential:', error);
      toast.error(error.response?.data?.message || 'Failed to update credential');
    }
  };

  const handleDeleteCredential = async (credentialId: number, title: string) => {
    if (!confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
      return;
    }

    try {
      await apiClient.delete(`/clients/${clientId}/credentials/${credentialId}`);
      toast.success('Credential deleted successfully');
      fetchCredentials();
    } catch (error: any) {
      console.error('Failed to delete credential:', error);
      toast.error(error.response?.data?.message || 'Failed to delete credential');
    }
  };

  const togglePasswordVisibility = async (credentialId: number) => {
    if (!showPassword[credentialId]) {
      try {
        const response = await apiClient.get(`/clients/${clientId}/credentials/${credentialId}/password`);
        setPasswords(prev => ({ ...prev, [credentialId]: response.data.data.password }));
      } catch (error) {
        console.error('Failed to get password:', error);
        toast.error('Failed to retrieve password');
        return;
      }
    }
    setShowPassword(prev => ({ ...prev, [credentialId]: !prev[credentialId] }));
  };

  const copyToClipboard = async (text: string, credentialId: number) => {
    try {
      await navigator.clipboard.writeText(text);
      setCopied(prev => ({ ...prev, [credentialId]: true }));
      setTimeout(() => setCopied(prev => ({ ...prev, [credentialId]: false })), 2000);
      toast.success('Copied to clipboard');
    } catch (error) {
      toast.error('Failed to copy to clipboard');
    }
  };

  const openEditDialog = (credential: Credential) => {
    setEditingCredential(credential);
    setFormData({
      title: credential.title,
      url: credential.url || '',
      username: credential.username || '',
      password: '', // Don't populate password for security
      notes: credential.notes || '',
      is_active: credential.is_active,
    });
    setIsEditDialogOpen(true);
  };

  const resetForm = () => {
    setFormData({
      title: '',
      url: '',
      username: '',
      password: '',
      notes: '',
      is_active: true,
    });
  };

  const openAddDialog = () => {
    resetForm();
    setIsAddDialogOpen(true);
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading credentials...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center gap-4">
          <Button
            variant="ghost"
            size="icon"
            onClick={() => router.push(`/clients/${clientId}`)}
          >
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div className="flex-1">
            <h1 className="text-2xl font-bold">Client Credentials</h1>
            <p className="text-muted-foreground">
              Manage login credentials for {client?.name}
            </p>
          </div>
          <Button onClick={openAddDialog}>
            <Plus className="mr-2 h-4 w-4" />
            Add Credential
          </Button>
        </div>

        {/* Credentials Grid */}
        {credentials.length > 0 ? (
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {credentials.map((credential) => (
              <Card key={credential.id} className="relative">
                <CardHeader>
                  <div className="flex justify-between items-start">
                    <div className="space-y-1">
                      <CardTitle className="text-lg">{credential.title}</CardTitle>
                      <CardDescription>
                        {credential.url && (
                          <div className="flex items-center gap-1">
                            <ExternalLink className="h-3 w-3" />
                            <a 
                              href={credential.url} 
                              target="_blank" 
                              rel="noopener noreferrer"
                              className="text-blue-600 hover:underline"
                            >
                              Visit Site
                            </a>
                          </div>
                        )}
                      </CardDescription>
                    </div>
                    <div className="flex items-center gap-2">
                      <Badge variant={credential.is_active ? "default" : "secondary"}>
                        {credential.is_active ? 'Active' : 'Inactive'}
                      </Badge>
                    </div>
                  </div>
                </CardHeader>
                <CardContent className="space-y-3">
                  {credential.username && (
                    <div className="space-y-1">
                      <Label className="text-xs text-muted-foreground">Username</Label>
                      <div className="flex items-center gap-2">
                        <Input 
                          value={credential.username} 
                          readOnly 
                          className="text-sm"
                        />
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => copyToClipboard(credential.username!, credential.id)}
                        >
                          {copied[credential.id] ? (
                            <Check className="h-4 w-4 text-green-600" />
                          ) : (
                            <Copy className="h-4 w-4" />
                          )}
                        </Button>
                      </div>
                    </div>
                  )}
                  
                  <div className="space-y-1">
                    <Label className="text-xs text-muted-foreground">Password</Label>
                    <div className="flex items-center gap-2">
                      <Input 
                        type={showPassword[credential.id] ? "text" : "password"}
                        value={showPassword[credential.id] ? passwords[credential.id] || '••••••••' : '••••••••'}
                        readOnly 
                        className="text-sm"
                      />
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => togglePasswordVisibility(credential.id)}
                      >
                        {showPassword[credential.id] ? (
                          <EyeOff className="h-4 w-4" />
                        ) : (
                          <Eye className="h-4 w-4" />
                        )}
                      </Button>
                      {showPassword[credential.id] && passwords[credential.id] && (
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => copyToClipboard(passwords[credential.id], credential.id)}
                        >
                          {copied[credential.id] ? (
                            <Check className="h-4 w-4 text-green-600" />
                          ) : (
                            <Copy className="h-4 w-4" />
                          )}
                        </Button>
                      )}
                    </div>
                  </div>

                  {credential.notes && (
                    <div className="space-y-1">
                      <Label className="text-xs text-muted-foreground">Notes</Label>
                      <p className="text-sm text-muted-foreground">{credential.notes}</p>
                    </div>
                  )}

                  <div className="flex items-center justify-between pt-2">
                    <div className="flex items-center gap-2">
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => openEditDialog(credential)}
                      >
                        <Edit className="h-3 w-3" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handleDeleteCredential(credential.id, credential.title)}
                        className="text-red-600 hover:text-red-700"
                      >
                        <Trash2 className="h-3 w-3" />
                      </Button>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <div className="text-center py-12">
            <p className="text-muted-foreground">No credentials found for this client.</p>
            <Button onClick={openAddDialog} className="mt-4">
              <Plus className="mr-2 h-4 w-4" />
              Add First Credential
            </Button>
          </div>
        )}

        {/* Add Credential Dialog */}
        <Dialog open={isAddDialogOpen} onOpenChange={setIsAddDialogOpen}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Add New Credential</DialogTitle>
              <DialogDescription>
                Add a new login credential for this client. Passwords are encrypted and stored securely.
              </DialogDescription>
            </DialogHeader>
            <div className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="title">Title *</Label>
                <Input
                  id="title"
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  placeholder="e.g., Website Login, Email Account"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="url">URL</Label>
                <Input
                  id="url"
                  type="url"
                  value={formData.url}
                  onChange={(e) => setFormData({ ...formData, url: e.target.value })}
                  placeholder="https://example.com"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="username">Username</Label>
                <Input
                  id="username"
                  value={formData.username}
                  onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                  placeholder="username@example.com"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="password">Password *</Label>
                <Input
                  id="password"
                  type="password"
                  value={formData.password}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                  placeholder="Enter password"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="notes">Notes</Label>
                <Textarea
                  id="notes"
                  value={formData.notes}
                  onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                  placeholder="Additional notes..."
                  rows={3}
                />
              </div>
              <div className="flex items-center space-x-2">
                <Switch
                  id="is_active"
                  checked={formData.is_active}
                  onCheckedChange={(checked) => setFormData({ ...formData, is_active: checked })}
                />
                <Label htmlFor="is_active">Active</Label>
              </div>
            </div>
            <DialogFooter>
              <Button variant="outline" onClick={() => setIsAddDialogOpen(false)}>
                Cancel
              </Button>
              <Button onClick={handleAddCredential} disabled={!formData.title || !formData.password}>
                Add Credential
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>

        {/* Edit Credential Dialog */}
        <Dialog open={isEditDialogOpen} onOpenChange={setIsEditDialogOpen}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Edit Credential</DialogTitle>
              <DialogDescription>
                Update the credential information. Leave password empty to keep the current password.
              </DialogDescription>
            </DialogHeader>
            <div className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="edit-title">Title *</Label>
                <Input
                  id="edit-title"
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  placeholder="e.g., Website Login, Email Account"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="edit-url">URL</Label>
                <Input
                  id="edit-url"
                  type="url"
                  value={formData.url}
                  onChange={(e) => setFormData({ ...formData, url: e.target.value })}
                  placeholder="https://example.com"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="edit-username">Username</Label>
                <Input
                  id="edit-username"
                  value={formData.username}
                  onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                  placeholder="username@example.com"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="edit-password">Password</Label>
                <Input
                  id="edit-password"
                  type="password"
                  value={formData.password}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                  placeholder="Leave empty to keep current password"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="edit-notes">Notes</Label>
                <Textarea
                  id="edit-notes"
                  value={formData.notes}
                  onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                  placeholder="Additional notes..."
                  rows={3}
                />
              </div>
              <div className="flex items-center space-x-2">
                <Switch
                  id="edit-is_active"
                  checked={formData.is_active}
                  onCheckedChange={(checked) => setFormData({ ...formData, is_active: checked })}
                />
                <Label htmlFor="edit-is_active">Active</Label>
              </div>
            </div>
            <DialogFooter>
              <Button variant="outline" onClick={() => setIsEditDialogOpen(false)}>
                Cancel
              </Button>
              <Button onClick={handleEditCredential} disabled={!formData.title}>
                Update Credential
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </MainLayout>
  );
}
