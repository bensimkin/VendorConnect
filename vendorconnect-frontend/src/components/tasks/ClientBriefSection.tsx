'use client';

import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import apiClient from '@/lib/api-client';
import { FileText, Upload, Trash2, Download, Edit3, Save, X, File, FolderOpen } from 'lucide-react';
import { toast } from 'react-hot-toast';
import { useAuthStore } from '@/lib/auth-store';

interface TaskFile {
  id: number;
  file_name: string;
  file_path: string;
  file_type: string;
  file_size: number;
  description: string | null;
  created_at: string;
}

interface ClientBrief {
  client_id: number;
  client_name: string;
  brief: string;
}

interface Client {
  id: number;
  name: string;
  company: string | null;
}

interface ClientBriefData {
  task_id: number;
  task_title: string;
  clients: Client[];
  client_briefs: ClientBrief[];
  brand_guide_files: TaskFile[];
  client_files: TaskFile[];
}

interface ClientBriefSectionProps {
  taskId: number;
  canEdit?: boolean;
  createdBy?: number;
}

export default function ClientBriefSection({ taskId, canEdit = false, createdBy }: ClientBriefSectionProps) {
  const { user } = useAuthStore();
  const [data, setData] = useState<ClientBriefData | null>(null);
  const [loading, setLoading] = useState(true);
  const [editingBrief, setEditingBrief] = useState(false);
  const [briefText, setBriefText] = useState('');
  const [savingBrief, setSavingBrief] = useState(false);
  const [showUploadForm, setShowUploadForm] = useState(false);
  const [uploadForm, setUploadForm] = useState({
    category: 'brand_guide' as 'brand_guide' | 'client_file',
    description: '',
  });
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [uploading, setUploading] = useState(false);

  useEffect(() => {
    fetchClientBriefData();
  }, [taskId]);

  const fetchClientBriefData = async () => {
    try {
      setLoading(true);
      const response = await apiClient.get(`/tasks/${taskId}/client-brief-files`);
      if (response.data.success) {
        setData(response.data.data);
        // Set brief text from the first client brief if available
        const firstBrief = response.data.data.client_briefs?.[0];
        setBriefText(firstBrief?.brief || '');
      }
    } catch (error) {
      console.error('Failed to fetch client brief data:', error);
      // Don't show error toast as this is optional data
    } finally {
      setLoading(false);
    }
  };

  const handleSaveBrief = async () => {
    if (!briefText.trim()) {
      toast.error('Client brief cannot be empty');
      return;
    }

    if (!data?.clients?.length) {
      toast.error('No clients found for this task');
      return;
    }

    setSavingBrief(true);
    try {
      // For now, update the first client's brief
      // In a more advanced implementation, you might want to let users choose which client
      const firstClient = data.clients[0];
      
      if (!firstClient || !firstClient.id) {
        toast.error('Invalid client data');
        return;
      }

      console.log('Saving brief for client:', firstClient.id, 'with text:', briefText);
      
      const response = await apiClient.put(`/clients/${firstClient.id}/client-brief`, {
        client_brief: briefText,
      });
      
      console.log('Save response:', response.data);
      toast.success('Client brief updated successfully');
      setEditingBrief(false);
      fetchClientBriefData();
    } catch (error: any) {
      console.error('Failed to update client brief:', error);
      console.error('Error response:', error.response?.data);
      if (error.response?.status === 403) {
        toast.error('You do not have permission to update this brief');
      } else if (error.response?.status === 422) {
        toast.error('Validation error: ' + (error.response?.data?.message || 'Invalid data'));
      } else {
        toast.error('Failed to update client brief');
      }
    } finally {
      setSavingBrief(false);
    }
  };

  const handleFileUpload = async () => {
    if (!selectedFile) {
      toast.error('Please select a file');
      return;
    }

    if (!data?.clients?.length) {
      toast.error('No clients found for this task');
      return;
    }

    setUploading(true);
    try {
      const formData = new FormData();
      formData.append('file', selectedFile);
      formData.append('file_category', uploadForm.category);
      if (uploadForm.description) {
        formData.append('description', uploadForm.description);
      }

      console.log('Uploading file for client:', data.clients[0].id, 'category:', uploadForm.category);

      const response = await apiClient.post(`/clients/${data.clients[0].id}/files`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      console.log('Upload response:', response.data);
      toast.success('File uploaded successfully');
      setShowUploadForm(false);
      setSelectedFile(null);
      setUploadForm({ category: 'brand_guide', description: '' });
      fetchClientBriefData();
    } catch (error: any) {
      console.error('Failed to upload file:', error);
      console.error('Error response:', error.response?.data);
      if (error.response?.status === 403) {
        toast.error('You do not have permission to upload files');
      } else if (error.response?.status === 422) {
        toast.error('Validation error: ' + (error.response?.data?.message || 'Invalid data'));
      } else {
        toast.error('Failed to upload file');
      }
    } finally {
      setUploading(false);
    }
  };

  const handleDeleteFile = async (fileId: number) => {
    if (!confirm('Are you sure you want to delete this file?')) return;

    try {
      await apiClient.delete(`/tasks/${taskId}/files/${fileId}`);
      toast.success('File deleted successfully');
      fetchClientBriefData();
    } catch (error: any) {
      console.error('Failed to delete file:', error);
      if (error.response?.status === 403) {
        toast.error('You do not have permission to delete this file');
      } else {
        toast.error('Failed to delete file');
      }
    }
  };

  const formatFileSize = (bytes: number): string => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
  };

  const getFileUrl = (filePath: string) => {
    // Get the API base URL from environment or construct it
    const apiUrl = process.env.NEXT_PUBLIC_API_URL || '';
    const baseUrl = apiUrl.replace('/api/v1', ''); // Remove /api/v1 to get base URL
    return `${baseUrl}/storage/${filePath}`;
  };

  // Check if current user can edit
  const userCanEdit = user && (
    user.roles?.some((role: any) => ['admin', 'sub_admin'].includes(role.name.toLowerCase())) ||
    (user.roles?.some((role: any) => role.name.toLowerCase() === 'requester') && user.id === createdBy)
  );

  if (loading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <FileText className="h-5 w-5" />
            Client Brief & Files
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="text-center py-4">
            <div className="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
            <p className="text-xs text-muted-foreground mt-2">Loading...</p>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <FileText className="h-5 w-5" />
          Client Brief & Files
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-6">
        {/* Client Brief */}
        <div>
          <div className="flex items-center justify-between mb-2">
            <h4 className="font-medium text-sm text-gray-700">Client Brief</h4>
            {userCanEdit && !editingBrief && (
              <Button
                variant="ghost"
                size="sm"
                onClick={() => setEditingBrief(true)}
                className="h-7 px-2"
              >
                <Edit3 className="h-3 w-3" />
              </Button>
            )}
          </div>

          {editingBrief ? (
            <div className="space-y-2">
              <Textarea
                value={briefText}
                onChange={(e) => setBriefText(e.target.value)}
                placeholder="Enter client brief, notes, or special instructions..."
                rows={6}
                className="text-sm"
              />
              <div className="flex gap-2">
                <Button
                  size="sm"
                  onClick={handleSaveBrief}
                  disabled={savingBrief}
                >
                  <Save className="h-3 w-3 mr-1" />
                  {savingBrief ? 'Saving...' : 'Save'}
                </Button>
                <Button
                  size="sm"
                  variant="outline"
                  onClick={() => {
                    setEditingBrief(false);
                    const firstBrief = data?.client_briefs?.[0];
                    setBriefText(firstBrief?.brief || '');
                  }}
                  disabled={savingBrief}
                >
                  <X className="h-3 w-3 mr-1" />
                  Cancel
                </Button>
              </div>
            </div>
          ) : (
            <div className="bg-blue-50 p-3 rounded-md">
              {data?.client_briefs && data.client_briefs.length > 0 ? (
                <div className="space-y-3">
                  {data.client_briefs.map((clientBrief, index) => (
                    <div key={clientBrief.client_id} className="border-b border-blue-200 pb-2 last:border-b-0 last:pb-0">
                      <div className="flex items-center gap-2 mb-1">
                        <Badge variant="outline" className="text-xs">
                          {clientBrief.client_name}
                        </Badge>
                      </div>
                      <p className="text-sm whitespace-pre-wrap text-gray-700">
                        {clientBrief.brief}
                      </p>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-sm text-gray-500 italic">
                  No client brief added yet
                  {userCanEdit && ' - Click the edit button to add one'}
                </p>
              )}
            </div>
          )}
        </div>

        {/* Brand Guide Files */}
        <div>
          <div className="flex items-center justify-between mb-2">
            <h4 className="font-medium text-sm text-gray-700 flex items-center gap-2">
              <FolderOpen className="h-4 w-4 text-purple-600" />
              Brand Guide Files
            </h4>
            <Badge variant="secondary" className="text-xs">
              {data?.brand_guide_files?.length || 0}
            </Badge>
          </div>
          {data && data.brand_guide_files && data.brand_guide_files.length > 0 ? (
            <ul className="space-y-2">
              {data.brand_guide_files.map((file) => (
                <li
                  key={file.id}
                  className="flex items-start justify-between p-2 hover:bg-gray-50 rounded border"
                >
                  <div className="flex-1 min-w-0">
                    <a
                      href={getFileUrl(file.file_path)}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-blue-600 hover:underline text-sm font-medium flex items-center gap-1"
                      title={file.description || file.file_name}
                    >
                      <File className="h-3 w-3 flex-shrink-0" />
                      <span className="truncate">{file.file_name}</span>
                    </a>
                    {file.description && (
                      <p className="text-xs text-gray-500 mt-0.5 truncate">
                        {file.description}
                      </p>
                    )}
                    <p className="text-xs text-gray-400 mt-0.5">
                      {formatFileSize(file.file_size)}
                    </p>
                  </div>
                  {userCanEdit && (
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => handleDeleteFile(file.id)}
                      className="h-7 w-7 p-0 ml-2"
                    >
                      <Trash2 className="h-3 w-3 text-red-600" />
                    </Button>
                  )}
                </li>
              ))}
            </ul>
          ) : (
            <div className="text-center py-3 bg-gray-50 rounded border border-dashed">
              <p className="text-xs text-gray-500">No brand guide files yet</p>
            </div>
          )}
        </div>

        {/* Client Files */}
        <div>
          <div className="flex items-center justify-between mb-2">
            <h4 className="font-medium text-sm text-gray-700 flex items-center gap-2">
              <File className="h-4 w-4 text-blue-600" />
              Client Files
            </h4>
            <Badge variant="secondary" className="text-xs">
              {data?.client_files?.length || 0}
            </Badge>
          </div>
          {data && data.client_files && data.client_files.length > 0 ? (
            <ul className="space-y-2">
              {data.client_files.map((file) => (
                <li
                  key={file.id}
                  className="flex items-start justify-between p-2 hover:bg-gray-50 rounded border"
                >
                  <div className="flex-1 min-w-0">
                    <a
                      href={getFileUrl(file.file_path)}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-blue-600 hover:underline text-sm font-medium flex items-center gap-1"
                      title={file.description || file.file_name}
                    >
                      <File className="h-3 w-3 flex-shrink-0" />
                      <span className="truncate">{file.file_name}</span>
                    </a>
                    {file.description && (
                      <p className="text-xs text-gray-500 mt-0.5 truncate">
                        {file.description}
                      </p>
                    )}
                    <p className="text-xs text-gray-400 mt-0.5">
                      {formatFileSize(file.file_size)}
                    </p>
                  </div>
                  {userCanEdit && (
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => handleDeleteFile(file.id)}
                      className="h-7 w-7 p-0 ml-2"
                    >
                      <Trash2 className="h-3 w-3 text-red-600" />
                    </Button>
                  )}
                </li>
              ))}
            </ul>
          ) : (
            <div className="text-center py-3 bg-gray-50 rounded border border-dashed">
              <p className="text-xs text-gray-500">No client files yet</p>
            </div>
          )}
        </div>

        {/* Upload Button */}
        {userCanEdit && (
          <div>
            {!showUploadForm ? (
              <Button
                onClick={() => setShowUploadForm(true)}
                size="sm"
                className="w-full"
                variant="outline"
              >
                <Upload className="h-4 w-4 mr-2" />
                Upload File
              </Button>
            ) : (
              <div className="space-y-3 border rounded-lg p-3 bg-gray-50">
                <div className="space-y-2">
                  <Label htmlFor="file_category" className="text-xs">Category *</Label>
                  <select
                    id="file_category"
                    value={uploadForm.category}
                    onChange={(e) =>
                      setUploadForm((prev) => ({
                        ...prev,
                        category: e.target.value as 'brand_guide' | 'client_file',
                      }))
                    }
                    className="w-full text-sm border rounded-md px-3 py-2"
                  >
                    <option value="brand_guide">Brand Guide</option>
                    <option value="client_file">Client File</option>
                  </select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="file_upload" className="text-xs">File * (Max 10MB)</Label>
                  <Input
                    id="file_upload"
                    type="file"
                    onChange={(e) => setSelectedFile(e.target.files?.[0] || null)}
                    className="text-sm"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.txt,.zip"
                  />
                  {selectedFile && (
                    <p className="text-xs text-gray-600">
                      📎 {selectedFile.name} ({formatFileSize(selectedFile.size)})
                    </p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="file_description" className="text-xs">Description</Label>
                  <Textarea
                    id="file_description"
                    value={uploadForm.description}
                    onChange={(e) =>
                      setUploadForm((prev) => ({ ...prev, description: e.target.value }))
                    }
                    placeholder="Optional file description..."
                    rows={2}
                    className="text-sm"
                  />
                </div>

                <div className="flex gap-2">
                  <Button
                    size="sm"
                    onClick={handleFileUpload}
                    disabled={!selectedFile || uploading}
                    className="flex-1"
                  >
                    <Upload className="h-3 w-3 mr-1" />
                    {uploading ? 'Uploading...' : 'Upload'}
                  </Button>
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => {
                      setShowUploadForm(false);
                      setSelectedFile(null);
                      setUploadForm({ category: 'brand_guide', description: '' });
                    }}
                    disabled={uploading}
                  >
                    Cancel
                  </Button>
                </div>
              </div>
            )}
          </div>
        )}

        {/* Empty State */}
        {(!data?.client_briefs || data.client_briefs.length === 0) &&
          (!data?.brand_guide_files || data.brand_guide_files.length === 0) &&
          (!data?.client_files || data.client_files.length === 0) &&
          !userCanEdit && (
            <div className="text-center py-6 bg-gray-50 rounded-lg">
              <FileText className="h-8 w-8 text-gray-400 mx-auto mb-2" />
              <p className="text-sm text-gray-500">No client brief or files yet</p>
            </div>
          )}
      </CardContent>
    </Card>
  );
}

