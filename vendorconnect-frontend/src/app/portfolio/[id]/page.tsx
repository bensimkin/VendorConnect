'use client';

import { useState, useEffect } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { toast } from 'react-hot-toast';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
  ArrowLeft, 
  Edit, 
  Download, 
  Eye, 
  Calendar,
  User,
  Folder,
  FileText,
  Image,
  Presentation,
  Archive,
  Trash2,
  Plus
} from 'lucide-react';
import { apiClient } from '@/lib/api-client';

interface Portfolio {
  id: number;
  title: string;
  description?: string;
  deliverable_type: 'design' | 'document' | 'presentation' | 'other';
  status: 'completed' | 'in_progress' | 'review';
  created_at: string;
  completed_at?: string;
  client: {
    id: number;
    name: string;
    company?: string;
  };
  task?: {
    id: number;
    title: string;
  };
  project?: {
    id: number;
    title: string;
  };
  createdBy: {
    id: number;
    name: string;
  };
  taskType?: {
    id: number;
    task_type: string;
  };
  media: Array<{
    id: number;
    file_name: string;
    mime_type: string;
    size: number;
    original_url: string;
    created_at: string;
  }>;
}

export default function PortfolioDetailPage() {
  const router = useRouter();
  const params = useParams();
  const portfolioId = params.id as string;
  
  const [portfolio, setPortfolio] = useState<Portfolio | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (portfolioId) {
      fetchPortfolio();
    }
  }, [portfolioId]);

  const fetchPortfolio = async () => {
    try {
      setLoading(true);
      const response = await apiClient.get(`/portfolios/${portfolioId}`);
      setPortfolio(response.data.data);
    } catch (error) {
      console.error('Failed to fetch portfolio:', error);
      toast.error('Failed to load portfolio details');
      router.push('/portfolio');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async () => {
    if (!confirm('Are you sure you want to delete this portfolio item? This action cannot be undone.')) {
      return;
    }

    try {
      await apiClient.delete(`/portfolios/${portfolioId}`);
      toast.success('Portfolio item deleted successfully');
      router.push('/portfolio');
    } catch (error) {
      console.error('Failed to delete portfolio:', error);
      toast.error('Failed to delete portfolio item');
    }
  };

  const getDeliverableTypeIcon = (type: string) => {
    switch (type) {
      case 'design':
        return <Image className="h-5 w-5" />;
      case 'document':
        return <FileText className="h-5 w-5" />;
      case 'presentation':
        return <Presentation className="h-5 w-5" />;
      default:
        return <Archive className="h-5 w-5" />;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed':
        return 'bg-green-100 text-green-800';
      case 'in_progress':
        return 'bg-blue-100 text-blue-800';
      case 'review':
        return 'bg-yellow-100 text-yellow-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  const getFileIcon = (mimeType: string) => {
    if (mimeType.startsWith('image/')) return <Image className="h-4 w-4" />;
    if (mimeType.includes('pdf')) return <FileText className="h-4 w-4" />;
    if (mimeType.includes('presentation') || mimeType.includes('powerpoint')) return <Presentation className="h-4 w-4" />;
    return <Archive className="h-4 w-4" />;
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="flex justify-center items-center h-64">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>
      </MainLayout>
    );
  }

  if (!portfolio) {
    return (
      <MainLayout>
        <div className="flex flex-col items-center justify-center h-64">
          <h2 className="text-xl font-semibold mb-2">Portfolio not found</h2>
          <Button onClick={() => router.push('/portfolio')}>
            <ArrowLeft className="h-4 w-4 mr-2" />
            Back to Portfolio
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
              onClick={() => router.push('/portfolio')}
            >
              <ArrowLeft className="h-4 w-4" />
            </Button>
            <div>
              <h1 className="text-2xl font-bold">{portfolio.title}</h1>
              <p className="text-muted-foreground">Portfolio item details</p>
            </div>
          </div>
          <div className="flex gap-2">
            <Button
              variant="outline"
              onClick={() => router.push(`/portfolio/${portfolioId}/edit`)}
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

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Main Content */}
          <div className="lg:col-span-2 space-y-6">
            {/* Portfolio Details */}
            <Card>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <CardTitle className="flex items-center gap-2">
                    {getDeliverableTypeIcon(portfolio.deliverable_type)}
                    Portfolio Details
                  </CardTitle>
                  <Badge className={getStatusColor(portfolio.status)}>
                    {portfolio.status.replace('_', ' ')}
                  </Badge>
                </div>
              </CardHeader>
              <CardContent className="space-y-4">
                {portfolio.description && (
                  <div>
                    <h3 className="font-medium mb-2">Description</h3>
                    <p className="text-muted-foreground">{portfolio.description}</p>
                  </div>
                )}

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <h3 className="font-medium">Client</h3>
                    <div className="flex items-center gap-2 text-muted-foreground">
                      <User className="h-4 w-4" />
                      <span>{portfolio.client.name}</span>
                      {portfolio.client.company && (
                        <span className="text-sm">({portfolio.client.company})</span>
                      )}
                    </div>
                  </div>

                  {portfolio.project && (
                    <div className="space-y-2">
                      <h3 className="font-medium">Project</h3>
                      <div className="flex items-center gap-2 text-muted-foreground">
                        <Folder className="h-4 w-4" />
                        <span>{portfolio.project.title}</span>
                      </div>
                    </div>
                  )}

                  {portfolio.taskType && (
                    <div className="space-y-2">
                      <h3 className="font-medium">Task Type</h3>
                      <div className="flex items-center gap-2 text-muted-foreground">
                        <FileText className="h-4 w-4" />
                        <span>{portfolio.taskType.task_type}</span>
                      </div>
                    </div>
                  )}

                  <div className="space-y-2">
                    <h3 className="font-medium">Created By</h3>
                    <div className="flex items-center gap-2 text-muted-foreground">
                      <User className="h-4 w-4" />
                      <span>{portfolio.createdBy.name}</span>
                    </div>
                  </div>

                  <div className="space-y-2">
                    <h3 className="font-medium">Created Date</h3>
                    <div className="flex items-center gap-2 text-muted-foreground">
                      <Calendar className="h-4 w-4" />
                      <span>{formatDate(portfolio.created_at)}</span>
                    </div>
                  </div>

                  {portfolio.completed_at && (
                    <div className="space-y-2">
                      <h3 className="font-medium">Completed Date</h3>
                      <div className="flex items-center gap-2 text-muted-foreground">
                        <Calendar className="h-4 w-4" />
                        <span>{formatDate(portfolio.completed_at)}</span>
                      </div>
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>

            {/* Files */}
            <Card>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <CardTitle>Files ({portfolio.media.length})</CardTitle>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => router.push(`/portfolio/${portfolioId}/edit`)}
                  >
                    <Plus className="h-4 w-4 mr-2" />
                    Add Files
                  </Button>
                </div>
              </CardHeader>
              <CardContent>
                {portfolio.media.length > 0 ? (
                  <div className="space-y-3">
                    {portfolio.media.map((file) => (
                      <div
                        key={file.id}
                        className="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50"
                      >
                        <div className="flex items-center gap-3">
                          {getFileIcon(file.mime_type)}
                          <div>
                            <p className="font-medium">{file.file_name}</p>
                            <p className="text-sm text-muted-foreground">
                              {formatFileSize(file.size)} • {formatDate(file.created_at)}
                            </p>
                          </div>
                        </div>
                        <div className="flex gap-2">
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => window.open(file.original_url, '_blank')}
                          >
                            <Eye className="h-4 w-4 mr-1" />
                            View
                          </Button>
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => {
                              const link = document.createElement('a');
                              link.href = file.original_url;
                              link.download = file.file_name;
                              link.click();
                            }}
                          >
                            <Download className="h-4 w-4 mr-1" />
                            Download
                          </Button>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <Archive className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                    <h3 className="text-lg font-medium mb-2">No files uploaded</h3>
                    <p className="text-muted-foreground mb-4">
                      Upload files to showcase this portfolio item
                    </p>
                    <Button
                      variant="outline"
                      onClick={() => router.push(`/portfolio/${portfolioId}/edit`)}
                    >
                      <Plus className="h-4 w-4 mr-2" />
                      Add Files
                    </Button>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Quick Actions */}
            <Card>
              <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <Button
                  variant="outline"
                  className="w-full justify-start"
                  onClick={() => router.push(`/clients/${portfolio.client.id}`)}
                >
                  <User className="h-4 w-4 mr-2" />
                  View Client Profile
                </Button>
                {portfolio.project && (
                  <Button
                    variant="outline"
                    className="w-full justify-start"
                    onClick={() => router.push(`/projects/${portfolio.project.id}`)}
                  >
                    <Folder className="h-4 w-4 mr-2" />
                    View Project
                  </Button>
                )}
                {portfolio.task && (
                  <Button
                    variant="outline"
                    className="w-full justify-start"
                    onClick={() => router.push(`/tasks/${portfolio.task.id}`)}
                  >
                    <FileText className="h-4 w-4 mr-2" />
                    View Task
                  </Button>
                )}
              </CardContent>
            </Card>

            {/* Portfolio Info */}
            <Card>
              <CardHeader>
                <CardTitle>Portfolio Info</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Type</p>
                  <p className="flex items-center gap-2">
                    {getDeliverableTypeIcon(portfolio.deliverable_type)}
                    {portfolio.deliverable_type.charAt(0).toUpperCase() + portfolio.deliverable_type.slice(1)}
                  </p>
                </div>
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Status</p>
                  <Badge className={getStatusColor(portfolio.status)}>
                    {portfolio.status.replace('_', ' ')}
                  </Badge>
                </div>
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Files</p>
                  <p>{portfolio.media.length} file{portfolio.media.length !== 1 ? 's' : ''}</p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </MainLayout>
  );
}
