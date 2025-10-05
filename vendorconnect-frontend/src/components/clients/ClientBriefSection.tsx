'use client';

import React, { useState, useEffect } from 'react';
import { toast } from 'react-hot-toast';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
  FileText, 
  Upload, 
  Download, 
  Trash2, 
  Edit, 
  Save, 
  X, 
  Plus,
  File,
  Image,
  Archive
} from 'lucide-react';
import { apiClient } from '@/lib/api-client';
import { useAuthStore } from '@/lib/auth-store';

interface ClientFile {
  id: number;
  file_name: string;
  file_path: string;
  file_type: string;
  file_size: number;
  description: string | null;
  created_at: string;
}

interface ClientBriefData {
  client_id: number;
  client_name: string;
  client_brief: string | null;
  brand_guide_files: ClientFile[];
  client_files: ClientFile[];
}

interface ClientBriefSectionProps {
  clientId: number;
}

export default function ClientBriefSection({ clientId }: ClientBriefSectionProps) {
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
  }, [clientId]);

  const fetchClientBriefData = async () => {
    try {
      setLoading(true);
      const response = await apiClient.get(`/clients/${clientId}/client-brief-files`);
      if (response.data.success) {
        setData(response.data.data);
        setBriefText(response.data.data.client_brief || '');
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

    setSavingBrief(true);
    try {
      console.log('Saving brief for client:', clientId, 'with text:', briefText);
      
      const response = await apiClient.put(`/clients/${clientId}/client-brief`, {
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

    setUploading(true);
    try {
      const formData = new FormData();
      formData.append('file', selectedFile);
      formData.append('file_category', uploadForm.category);
      if (uploadForm.description) {
        formData.append('description', uploadForm.description);
      }

      console.log('Uploading file for client:', clientId, 'category:', uploadForm.category);

      const response = await apiClient.post(`/clients/${clientId}/files`, formData, {
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
      await apiClient.delete(`/clients/${clientId}/files/${fileId}`);
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

  const getFileIcon = (fileType: string) => {
    if (fileType.startsWith('image/')) return <Image className="h-4 w-4" />;
    if (fileType.includes('pdf')) return <FileText className="h-4 w-4" />;
    if (fileType.includes('zip') || fileType.includes('rar')) return <Archive className="h-4 w-4" />;
    return <File className="h-4 w-4" />;
  };

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  const userCanEdit = user?.roles?.some((role: any) => ['admin', 'sub_admin'].includes(role.name.toLowerCase()));

  if (loading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Client Brief & Files</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-center justify-center py-8">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
            <span className="ml-2">Loading...</span>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center justify-between">
          <CardTitle>Client Brief & Files</CardTitle>
          {userCanEdit && (
            <div className="flex gap-2">
              <Button
                size="sm"
                variant="outline"
                onClick={() => setShowUploadForm(!showUploadForm)}
              >
                <Plus className="h-3 w-3 mr-1" />
                Upload File
              </Button>
            </div>
          )}
        </div>
      </CardHeader>
      <CardContent className="space-y-6">
        {/* Client Brief */}
        <div>
          <div className="flex items-center justify-between mb-3">
            <h4 className="text-sm font-medium text-gray-900">Client Brief</h4>
            {userCanEdit && !editingBrief && (
              <Button
                size="sm"
                variant="outline"
                onClick={() => setEditingBrief(true)}
              >
                <Edit className="h-3 w-3 mr-1" />
                Edit
              </Button>
            )}
          </div>

          {editingBrief ? (
            <div className="space-y-3">
              <Textarea
                value={briefText}
                onChange={(e) => setBriefText(e.target.value)}
                placeholder="Enter client brief..."
                rows={4}
                className="w-full"
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
                    setBriefText(data?.client_brief || '');
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
              {data?.client_brief ? (
                <p className="text-sm whitespace-pre-wrap text-gray-700">
                  {data.client_brief}
                </p>
              ) : (
                <p className="text-sm text-gray-500 italic">
                  No client brief added yet
                  {userCanEdit && ' - Click the edit button to add one'}
                </p>
              )}
            </div>
          )}
        </div>

        {/* File Upload Form */}
        {showUploadForm && userCanEdit && (
          <div className="border rounded-lg p-4 bg-gray-50">
            <h4 className="text-sm font-medium mb-3">Upload File</h4>
            <div className="space-y-3">
              <div>
                <Label htmlFor="file">File</Label>
                <Input
                  id="file"
                  type="file"
                  onChange={(e) => setSelectedFile(e.target.files?.[0] || null)}
                  className="mt-1"
                />
              </div>
              <div>
                <Label htmlFor="category">Category</Label>
                <Select
                  value={uploadForm.category}
                  onValueChange={(value: 'brand_guide' | 'client_file') =>
                    setUploadForm({ ...uploadForm, category: value })
                  }
                >
                  <SelectTrigger className="mt-1">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="brand_guide">Brand Guide</SelectItem>
                    <SelectItem value="client_file">Client File</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div>
                <Label htmlFor="description">Description (Optional)</Label>
                <Input
                  id="description"
                  value={uploadForm.description}
                  onChange={(e) =>
                    setUploadForm({ ...uploadForm, description: e.target.value })
                  }
                  placeholder="Brief description of the file"
                  className="mt-1"
                />
              </div>
              <div className="flex gap-2">
                <Button
                  size="sm"
                  onClick={handleFileUpload}
                  disabled={!selectedFile || uploading}
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
          </div>
        )}

        {/* Brand Guide Files */}
        <div>
          <h4 className="text-sm font-medium text-gray-900 mb-3">Brand Guide Files</h4>
          {data?.brand_guide_files && data.brand_guide_files.length > 0 ? (
            <div className="space-y-2">
              {data.brand_guide_files.map((file) => (
                <div
                  key={file.id}
                  className="flex items-center justify-between p-3 bg-white border rounded-lg"
                >
                  <div className="flex items-center space-x-3">
                    {getFileIcon(file.file_type)}
                    <div>
                      <p className="text-sm font-medium">{file.file_name}</p>
                      <p className="text-xs text-gray-500">
                        {formatFileSize(file.file_size)} • {file.description || 'No description'}
                      </p>
                    </div>
                  </div>
                  <div className="flex items-center space-x-2">
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => {
                        // Download file logic here
                        window.open(`/storage/${file.file_path}`, '_blank');
                      }}
                    >
                      <Download className="h-3 w-3" />
                    </Button>
                    {userCanEdit && (
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => handleDeleteFile(file.id)}
                      >
                        <Trash2 className="h-3 w-3" />
                      </Button>
                    )}
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-sm text-gray-500 italic">No brand guide files uploaded yet</p>
          )}
        </div>

        {/* Client Files */}
        <div>
          <h4 className="text-sm font-medium text-gray-900 mb-3">Client Files</h4>
          {data?.client_files && data.client_files.length > 0 ? (
            <div className="space-y-2">
              {data.client_files.map((file) => (
                <div
                  key={file.id}
                  className="flex items-center justify-between p-3 bg-white border rounded-lg"
                >
                  <div className="flex items-center space-x-3">
                    {getFileIcon(file.file_type)}
                    <div>
                      <p className="text-sm font-medium">{file.file_name}</p>
                      <p className="text-xs text-gray-500">
                        {formatFileSize(file.file_size)} • {file.description || 'No description'}
                      </p>
                    </div>
                  </div>
                  <div className="flex items-center space-x-2">
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => {
                        // Download file logic here
                        window.open(`/storage/${file.file_path}`, '_blank');
                      }}
                    >
                      <Download className="h-3 w-3" />
                    </Button>
                    {userCanEdit && (
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => handleDeleteFile(file.id)}
                      >
                        <Trash2 className="h-3 w-3" />
                      </Button>
                    )}
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-sm text-gray-500 italic">No client files uploaded yet</p>
          )}
        </div>

        {/* Empty State */}
        {(!data?.client_brief && 
          (!data?.brand_guide_files || data.brand_guide_files.length === 0) &&
          (!data?.client_files || data.client_files.length === 0) &&
          !userCanEdit) && (
          <div className="text-center py-6 bg-gray-50 rounded-lg">
            <FileText className="h-8 w-8 text-gray-400 mx-auto mb-2" />
            <p className="text-sm text-gray-500">No client brief or files yet</p>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
