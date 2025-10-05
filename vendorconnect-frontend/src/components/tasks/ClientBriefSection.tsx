'use client';

import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import apiClient from '@/lib/api-client';
import { FileText, Download, File, FolderOpen, ExternalLink } from 'lucide-react';
import { toast } from 'react-hot-toast';

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
}

export default function ClientBriefSection({ taskId }: ClientBriefSectionProps) {
  const [data, setData] = useState<ClientBriefData | null>(null);
  const [loading, setLoading] = useState(true);

  const fetchClientBriefData = async () => {
    try {
      setLoading(true);
      const response = await apiClient.get(`/tasks/${taskId}/client-brief-files`);
      
      if (response.data.success) {
        setData(response.data.data);
      } else {
        toast.error('Failed to load client brief data');
      }
    } catch (error: any) {
      console.error('Failed to fetch client brief data:', error);
      toast.error('Failed to load client brief data');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchClientBriefData();
  }, [taskId]);

  const getFileIcon = (fileType: string) => {
    if (fileType.startsWith('image/')) {
      return <File className="h-4 w-4" />;
    } else if (fileType.includes('pdf')) {
      return <FileText className="h-4 w-4" />;
    } else if (fileType.includes('zip') || fileType.includes('rar')) {
      return <FolderOpen className="h-4 w-4" />;
    } else {
      return <File className="h-4 w-4" />;
    }
  };

  const formatFileSize = (bytes: number): string => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
  };

  const getFileUrl = (filePath: string) => {
    const apiUrl = process.env.NEXT_PUBLIC_API_URL || '';
    const baseUrl = apiUrl.replace('/api/v1', '');
    return `${baseUrl}/storage/${filePath}`;
  };

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
          <h4 className="font-medium text-sm text-gray-700 mb-2">Client Brief</h4>
          {data?.client_briefs && data.client_briefs.length > 0 ? (
            <div className="space-y-3">
              {data.client_briefs.map((brief) => (
                <div key={brief.client_id} className="bg-blue-50 p-3 rounded-md">
                  <div className="flex items-center justify-between mb-2">
                    <span className="text-xs font-medium text-blue-800">
                      {brief.client_name}
                    </span>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => window.open(`/clients/${brief.client_id}`, '_blank')}
                      className="h-6 px-2 text-blue-600 hover:text-blue-800"
                    >
                      <ExternalLink className="h-3 w-3 mr-1" />
                      Edit on Client Page
                    </Button>
                  </div>
                  <div className="text-sm whitespace-pre-wrap text-gray-700 break-words overflow-hidden overflow-y-auto max-h-60" style={{ wordBreak: 'break-word', overflowWrap: 'anywhere' }}>
                    {brief.brief}
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="bg-gray-50 p-3 rounded-md">
              <p className="text-sm text-gray-500 italic">
                No client brief added yet
              </p>
            </div>
          )}
        </div>

        {/* Brand Guide Files */}
        <div>
          <h4 className="font-medium text-sm text-gray-700 mb-2">Brand Guide Files</h4>
          {data?.brand_guide_files && data.brand_guide_files.length > 0 ? (
            <div className="space-y-2">
              {data.brand_guide_files.map((file) => (
                <div key={file.id} className="flex items-center justify-between p-3 bg-white border rounded-lg">
                  <div className="flex items-center space-x-3">
                    {getFileIcon(file.file_type)}
                    <div>
                      <p className="text-sm font-medium">{file.file_name}</p>
                      <p className="text-xs text-gray-500">
                        {formatFileSize(file.file_size)} • {file.description || 'No description'}
                      </p>
                    </div>
                  </div>
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={() => window.open(getFileUrl(file.file_path), '_blank')}
                    className="h-8 w-8 p-0"
                  >
                    <Download className="h-4 w-4" />
                  </Button>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-sm text-gray-500 italic">No brand guide files uploaded yet</p>
          )}
        </div>

        {/* Client Files */}
        <div>
          <h4 className="font-medium text-sm text-gray-700 mb-2">Client Files</h4>
          {data?.client_files && data.client_files.length > 0 ? (
            <div className="space-y-2">
              {data.client_files.map((file) => (
                <div key={file.id} className="flex items-center justify-between p-3 bg-white border rounded-lg">
                  <div className="flex items-center space-x-3">
                    {getFileIcon(file.file_type)}
                    <div>
                      <p className="text-sm font-medium">{file.file_name}</p>
                      <p className="text-xs text-gray-500">
                        {formatFileSize(file.file_size)} • {file.description || 'No description'}
                      </p>
                    </div>
                  </div>
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={() => window.open(getFileUrl(file.file_path), '_blank')}
                    className="h-8 w-8 p-0"
                  >
                    <Download className="h-4 w-4" />
                  </Button>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-sm text-gray-500 italic">No client files uploaded yet</p>
          )}
        </div>

        {/* Empty state */}
        {(!data?.client_briefs || data.client_briefs.length === 0) &&
         (!data?.brand_guide_files || data.brand_guide_files.length === 0) &&
         (!data?.client_files || data.client_files.length === 0) && (
          <div className="text-center py-6 bg-gray-50 rounded-lg">
            <FileText className="h-8 w-8 text-gray-400 mx-auto mb-2" />
            <p className="text-sm text-gray-500">No client brief or files yet</p>
            <p className="text-xs text-gray-400 mt-1">
              Visit the client page to add briefs and files
            </p>
          </div>
        )}
      </CardContent>
    </Card>
  );
}