'use client';

import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Key, Plus, Copy, Trash2, RotateCcw, Eye, EyeOff, Calendar, Shield, BookOpen } from 'lucide-react';
import apiClient from '@/lib/api-client';
import { toast } from 'react-hot-toast';
import { format } from 'date-fns';

interface ApiKey {
  id: number;
  name: string;
  description: string | null;
  masked_key: string;
  key?: string; // Only present when creating/regenerating
  permissions: string[] | null;
  last_used_at: string | null;
  expires_at: string | null;
  is_active: boolean;
  created_at: string;
}

interface ApiKeyStats {
  total_keys: number;
  active_keys: number;
  expired_keys: number;
  recently_used: number;
  max_keys: number;
}

export default function ApiKeyManager() {
  const [apiKeys, setApiKeys] = useState<ApiKey[]>([]);
  const [stats, setStats] = useState<ApiKeyStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [creating, setCreating] = useState(false);
  const [showCreateDialog, setShowCreateDialog] = useState(false);
  const [showKeyDialog, setShowKeyDialog] = useState(false);
  const [newKey, setNewKey] = useState<ApiKey | null>(null);
  const [showKey, setShowKey] = useState(false);

  // Form state
  const [formData, setFormData] = useState({
    name: '',
    description: '',
    permissions: [] as string[],
    expires_at: '',
  });

  const permissionOptions = [
    { value: 'create', label: 'Create (POST)' },
    { value: 'read', label: 'Read (GET)' },
    { value: 'update', label: 'Update (PUT/PATCH)' },
    { value: 'delete', label: 'Delete (DELETE)' },
  ];

  useEffect(() => {
    fetchApiKeys();
    fetchStats();
  }, []);

  const fetchApiKeys = async () => {
    try {
      const response = await apiClient.get('/api-keys');
      setApiKeys(response.data.data);
    } catch (error: any) {
      toast.error('Failed to fetch API keys');
    } finally {
      setLoading(false);
    }
  };

  const fetchStats = async () => {
    try {
      const response = await apiClient.get('/api-keys/stats');
      setStats(response.data.data);
    } catch (error: any) {
      console.error('Failed to fetch API key stats:', error);
    }
  };

  const handleCreateKey = async () => {
    if (!formData.name.trim()) {
      toast.error('Please enter a name for the API key');
      return;
    }

    setCreating(true);
    try {
      const response = await apiClient.post('/api-keys', formData);
      const createdKey = response.data.data;
      setNewKey(createdKey);
      setShowKey(true); // Show the full key initially
      setShowCreateDialog(false);
      setShowKeyDialog(true);
      setFormData({ name: '', description: '', permissions: [], expires_at: '' });
      fetchApiKeys();
      fetchStats();
      toast.success('API key created successfully');
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to create API key');
    } finally {
      setCreating(false);
    }
  };

  const handleDeleteKey = async (id: number) => {
    if (!confirm('Are you sure you want to delete this API key? This action cannot be undone.')) {
      return;
    }

    try {
      await apiClient.delete(`/api-keys/${id}`);
      setApiKeys(apiKeys.filter(key => key.id !== id));
      fetchStats();
      toast.success('API key deleted successfully');
    } catch (error: any) {
      toast.error('Failed to delete API key');
    }
  };

  const handleRegenerateKey = async (id: number) => {
    if (!confirm('Are you sure you want to regenerate this API key? The old key will no longer work.')) {
      return;
    }

    try {
      const response = await apiClient.post(`/api-keys/${id}/regenerate`);
      const regeneratedKey = response.data.data;
      setNewKey(regeneratedKey);
      setShowKey(true); // Show the full key initially
      setShowKeyDialog(true);
      fetchApiKeys();
      toast.success('API key regenerated successfully');
    } catch (error: any) {
      toast.error('Failed to regenerate API key');
    }
  };

  const handleToggleActive = async (id: number, isActive: boolean) => {
    try {
      await apiClient.put(`/api-keys/${id}`, { is_active: isActive });
      setApiKeys(apiKeys.map(key => 
        key.id === id ? { ...key, is_active: isActive } : key
      ));
      fetchStats();
      toast.success(`API key ${isActive ? 'activated' : 'deactivated'} successfully`);
    } catch (error: any) {
      toast.error('Failed to update API key');
    }
  };

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    toast.success('Copied to clipboard');
  };

  const getStatusBadge = (key: ApiKey) => {
    if (!key.is_active) {
      return <Badge variant="secondary">Inactive</Badge>;
    }
    if (key.expires_at && new Date(key.expires_at) < new Date()) {
      return <Badge variant="destructive">Expired</Badge>;
    }
    return <Badge variant="default">Active</Badge>;
  };

  const getPermissionBadges = (permissions: string[] | null) => {
    if (!permissions || permissions.length === 0) {
      return <Badge variant="outline">All Permissions</Badge>;
    }
    return permissions.map(permission => (
      <Badge key={permission} variant="outline" className="mr-1">
        {permission}
      </Badge>
    ));
  };

  if (loading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center">
            <Key className="h-5 w-5 mr-2" />
            API Keys
          </CardTitle>
          <CardDescription>Loading...</CardDescription>
        </CardHeader>
      </Card>
    );
  }

  const generateSimpleExample = (apiKey: string) => {
    const baseUrl = window.location.origin.replace('3000', '8000');
    return `curl -X GET "${baseUrl}/api/v1/dashboard" \\
  -H "X-API-Key: ${apiKey}" \\
  -H "Content-Type: application/json"`;
  };

  return (
    <>
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <div>
              <CardTitle className="flex items-center">
                <Key className="h-5 w-5 mr-2" />
                API Keys
              </CardTitle>
              <CardDescription>
                Manage your API keys for external application access
              </CardDescription>
            </div>
            <Dialog open={showCreateDialog} onOpenChange={setShowCreateDialog}>
              <DialogTrigger asChild>
                <Button>
                  <Plus className="h-4 w-4 mr-2" />
                  Create API Key
                </Button>
              </DialogTrigger>
              <DialogContent className="sm:max-w-md">
                <DialogHeader>
                  <DialogTitle>Create New API Key</DialogTitle>
                  <DialogDescription>
                    Create a new API key for external application access
                  </DialogDescription>
                </DialogHeader>
                <div className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="name">Name *</Label>
                    <Input
                      id="name"
                      value={formData.name}
                      onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                      placeholder="e.g., My API Key Name"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="description">Description</Label>
                    <Textarea
                      id="description"
                      value={formData.description}
                      onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                      placeholder="Optional description for this API key"
                      rows={3}
                    />
                  </div>
                  <div className="space-y-2">
                    <Label>Permissions</Label>
                    <div className="space-y-2">
                      {permissionOptions.map(option => (
                        <div key={option.value} className="flex items-center space-x-2">
                          <input
                            type="checkbox"
                            id={option.value}
                            checked={formData.permissions.includes(option.value)}
                            onChange={(e) => {
                              if (e.target.checked) {
                                setFormData({
                                  ...formData,
                                  permissions: [...formData.permissions, option.value]
                                });
                              } else {
                                setFormData({
                                  ...formData,
                                  permissions: formData.permissions.filter(p => p !== option.value)
                                });
                              }
                            }}
                            className="rounded"
                          />
                          <Label htmlFor={option.value} className="text-sm">
                            {option.label}
                          </Label>
                        </div>
                      ))}
                      {formData.permissions.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                          No permissions selected = All permissions granted
                        </p>
                      )}
                    </div>
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="expires_at">Expires At (Optional)</Label>
                    <Input
                      id="expires_at"
                      type="datetime-local"
                      value={formData.expires_at}
                      onChange={(e) => setFormData({ ...formData, expires_at: e.target.value })}
                    />
                  </div>
                  <div className="flex justify-end space-x-2">
                    <Button
                      variant="outline"
                      onClick={() => setShowCreateDialog(false)}
                    >
                      Cancel
                    </Button>
                    <Button onClick={handleCreateKey} disabled={creating}>
                      {creating ? 'Creating...' : 'Create Key'}
                    </Button>
                  </div>
                </div>
              </DialogContent>
            </Dialog>
          </div>
        </CardHeader>
        <CardContent>
          {stats && (
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
              <div className="text-center">
                <div className="text-2xl font-bold">{stats.active_keys}</div>
                <div className="text-sm text-muted-foreground">Active Keys</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold">{stats.total_keys}</div>
                <div className="text-sm text-muted-foreground">Total Keys</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold">{stats.recently_used}</div>
                <div className="text-sm text-muted-foreground">Recently Used</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold">{stats.max_keys - stats.total_keys}</div>
                <div className="text-sm text-muted-foreground">Remaining</div>
              </div>
            </div>
          )}

          {apiKeys.length === 0 ? (
            <div className="text-center py-8">
              <Key className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
              <h3 className="text-lg font-medium mb-2">No API Keys</h3>
              <p className="text-muted-foreground mb-4">
                Create your first API key to start using external applications
              </p>
              <Button onClick={() => setShowCreateDialog(true)}>
                <Plus className="h-4 w-4 mr-2" />
                Create API Key
              </Button>
            </div>
          ) : (
            <div className="space-y-4">
              {apiKeys.map((key) => (
                <div key={key.id} className="border rounded-lg p-4">
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <div className="flex items-center space-x-2 mb-2">
                        <h3 className="font-medium">{key.name}</h3>
                        {getStatusBadge(key)}
                      </div>
                      {key.description && (
                        <p className="text-sm text-muted-foreground mb-2">
                          {key.description}
                        </p>
                      )}
                      <div className="flex items-center space-x-2 mb-2">
                        <code className="text-sm bg-muted px-2 py-1 rounded">
                          {key.masked_key}
                        </code>
                      </div>
                      <div className="flex items-center space-x-4 text-sm text-muted-foreground">
                        <div className="flex items-center">
                          <Shield className="h-4 w-4 mr-1" />
                          {getPermissionBadges(key.permissions)}
                        </div>
                        {key.last_used_at && (
                          <div className="flex items-center">
                            <Calendar className="h-4 w-4 mr-1" />
                            Last used: {format(new Date(key.last_used_at), 'MMM d, yyyy')}
                          </div>
                        )}
                        {key.expires_at && (
                          <div className="flex items-center">
                            <Calendar className="h-4 w-4 mr-1" />
                            Expires: {format(new Date(key.expires_at), 'MMM d, yyyy')}
                          </div>
                        )}
                      </div>
                    </div>
                    <div className="flex items-center space-x-2">
                      <Switch
                        checked={key.is_active}
                        onCheckedChange={(checked) => handleToggleActive(key.id, checked)}
                      />
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handleRegenerateKey(key.id)}
                      >
                        <RotateCcw className="h-4 w-4" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handleDeleteKey(key.id)}
                        className="text-destructive hover:text-destructive"
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>

      {/* Simple API Usage */}
      {apiKeys.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center">
              <BookOpen className="h-5 w-5 mr-2" />
              How to Use Your API Key
            </CardTitle>
            <CardDescription>
              Include your API key in the X-API-Key header for all requests
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 className="font-medium text-blue-900 mb-2">Simple Usage:</h4>
                <p className="text-sm text-blue-800 mb-3">
                  Add your API key to the <code className="bg-blue-100 px-1 rounded">X-API-Key</code> header in all API requests.
                </p>
                
                {apiKeys.filter(key => key.is_active).slice(0, 1).map((key) => {
                  const example = generateSimpleExample(key.masked_key);
                  return (
                    <div key={key.id}>
                      <div className="flex items-center justify-between mb-2">
                        <Label className="text-sm font-medium">Example with {key.name}:</Label>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => copyToClipboard(example)}
                        >
                          <Copy className="h-4 w-4" />
                        </Button>
                      </div>
                      <pre className="bg-muted p-3 rounded-lg text-sm overflow-x-auto">
                        <code>{example}</code>
                      </pre>
                    </div>
                  );
                })}
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Show API Key Dialog */}
      <Dialog open={showKeyDialog} onOpenChange={(open) => {
        setShowKeyDialog(open);
        if (!open) {
          setShowKey(false); // Reset to hidden state when dialog closes
          setNewKey(null); // Clear the key data when dialog closes
        }
      }}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>API Key Created</DialogTitle>
            <DialogDescription>
              Your API key has been created. Copy it now as you won't be able to see it again.
            </DialogDescription>
          </DialogHeader>
          {newKey && (
            <div className="space-y-4">
              <div className="space-y-2">
                <Label>API Key</Label>
                <div className="flex items-center space-x-2">
                  <Input
                    value={newKey.key}
                    readOnly
                    type={showKey ? 'text' : 'password'}
                    className="font-mono"
                    placeholder={!newKey.key && !newKey.masked_key ? "Loading..." : ""}
                  />
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setShowKey(!showKey)}
                    disabled={!newKey.key && !newKey.masked_key}
                  >
                    {showKey ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => copyToClipboard(showKey ? (newKey.key || '') : (newKey.masked_key || ''))}
                    disabled={!newKey.key && !newKey.masked_key}
                  >
                    <Copy className="h-4 w-4" />
                  </Button>
                </div>
              </div>
              <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p className="text-sm text-yellow-800">
                  <strong>Important:</strong> Store this API key securely. You won't be able to see it again after closing this dialog.
                </p>
              </div>
              <div className="flex justify-end">
                <Button onClick={() => {
                  setShowKeyDialog(false);
                  setShowKey(false); // Reset to hidden state
                  setNewKey(null); // Clear the key data
                }}>
                  I've Saved It
                </Button>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>
    </>
  );
}

