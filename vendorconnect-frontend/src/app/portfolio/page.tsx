'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { toast } from 'react-hot-toast';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { 
  Search, 
  Filter, 
  Plus, 
  Eye, 
  Download, 
  Calendar,
  User,
  Folder,
  FileText,
  Image,
  Presentation,
  Archive
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
    first_name?: string;
    last_name?: string;
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
  file_count: number;
  main_file?: {
    id: number;
    file_name: string;
    mime_type: string;
    original_url: string;
  };
}

interface Client {
  id: number;
  name: string;
  first_name?: string;
  last_name?: string;
  company?: string;
}

interface TaskType {
  id: number;
  task_type: string;
}

export default function PortfolioPage() {
  const router = useRouter();
  const [portfolios, setPortfolios] = useState<Portfolio[]>([]);
  const [clients, setClients] = useState<Client[]>([]);
  const [taskTypes, setTaskTypes] = useState<TaskType[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [filters, setFilters] = useState({
    client_id: '',
    task_type_id: '',
    deliverable_type: '',
    status: '',
  });

  useEffect(() => {
    fetchData();
  }, [filters]);

  const fetchData = async () => {
    try {
      setLoading(true);
      
      // Fetch portfolios with filters
      const params = new URLSearchParams();
      if (search) params.append('search', search);
      if (filters.client_id) params.append('client_id', filters.client_id);
      if (filters.task_type_id) params.append('task_type_id', filters.task_type_id);
      if (filters.deliverable_type) params.append('deliverable_type', filters.deliverable_type);
      if (filters.status) params.append('status', filters.status);

      const [portfolioRes, clientsRes, taskTypesRes] = await Promise.all([
        apiClient.get(`/portfolios?${params.toString()}`),
        apiClient.get('/clients'),
        apiClient.get('/task-types?per_page=all'),
      ]);

      setPortfolios(portfolioRes.data.data?.data || portfolioRes.data.data || []);
      setClients(clientsRes.data.data?.data || clientsRes.data.data || []);
      setTaskTypes(taskTypesRes.data.data || []);
    } catch (error) {
      console.error('Failed to fetch portfolio data:', error);
      toast.error('Failed to load portfolio data');
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = () => {
    fetchData();
  };

  const clearFilters = () => {
    setFilters({
      client_id: '',
      task_type_id: '',
      deliverable_type: '',
      status: '',
    });
    setSearch('');
  };

  const getDeliverableTypeIcon = (type: string) => {
    switch (type) {
      case 'design':
        return <Image className="h-4 w-4" />;
      case 'document':
        return <FileText className="h-4 w-4" />;
      case 'presentation':
        return <Presentation className="h-4 w-4" />;
      default:
        return <Archive className="h-4 w-4" />;
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
    return new Date(dateString).toLocaleDateString();
  };

  return (
    <MainLayout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold">Portfolio</h1>
            <p className="text-muted-foreground">View all client deliverables and work samples</p>
          </div>
          <Button onClick={() => router.push('/portfolio/new')}>
            <Plus className="h-4 w-4 mr-2" />
            Add Portfolio Item
          </Button>
        </div>

        {/* Filters */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Filter className="h-5 w-5" />
              Filters
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
              <div className="space-y-2">
                <label className="text-sm font-medium">Search</label>
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Search portfolios..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
                    className="pl-10"
                  />
                </div>
              </div>

              <div className="space-y-2">
                <label className="text-sm font-medium">Client</label>
                <select
                  value={filters.client_id}
                  onChange={(e) => setFilters({ ...filters, client_id: e.target.value })}
                  className="w-full px-3 py-2 border border-input rounded-md bg-background"
                >
                  <option value="">All Clients</option>
                  {clients.map((client) => (
                    <option key={client.id} value={client.id}>
                      {`${client.first_name || ''} ${client.last_name || ''}`.trim()} {client.company ? `(${client.company})` : ''}
                    </option>
                  ))}
                </select>
              </div>

              <div className="space-y-2">
                <label className="text-sm font-medium">Task Type</label>
                <select
                  value={filters.task_type_id}
                  onChange={(e) => setFilters({ ...filters, task_type_id: e.target.value })}
                  className="w-full px-3 py-2 border border-input rounded-md bg-background"
                >
                  <option value="">All Task Types</option>
                  {taskTypes.map((type) => (
                    <option key={type.id} value={type.id}>
                      {type.task_type}
                    </option>
                  ))}
                </select>
              </div>

              <div className="space-y-2">
                <label className="text-sm font-medium">Deliverable Type</label>
                <select
                  value={filters.deliverable_type}
                  onChange={(e) => setFilters({ ...filters, deliverable_type: e.target.value })}
                  className="w-full px-3 py-2 border border-input rounded-md bg-background"
                >
                  <option value="">All Types</option>
                  <option value="design">Design</option>
                  <option value="document">Document</option>
                  <option value="presentation">Presentation</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div className="space-y-2">
                <label className="text-sm font-medium">Status</label>
                <select
                  value={filters.status}
                  onChange={(e) => setFilters({ ...filters, status: e.target.value })}
                  className="w-full px-3 py-2 border border-input rounded-md bg-background"
                >
                  <option value="">All Statuses</option>
                  <option value="completed">Completed</option>
                  <option value="in_progress">In Progress</option>
                  <option value="review">Review</option>
                </select>
              </div>
            </div>

            <div className="flex justify-between items-center">
              <Button variant="outline" onClick={clearFilters}>
                Clear Filters
              </Button>
              <Button onClick={handleSearch}>
                Apply Filters
              </Button>
            </div>
          </CardContent>
        </Card>

        {/* Portfolio Grid */}
        {loading ? (
          <div className="flex justify-center items-center h-64">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {portfolios.map((portfolio) => (
              <Card key={portfolio.id} className="hover:shadow-lg transition-shadow">
                <CardHeader className="pb-3">
                  <div className="flex items-start justify-between">
                    <div className="flex items-center gap-2">
                      {getDeliverableTypeIcon(portfolio.deliverable_type)}
                      <CardTitle className="text-lg">{portfolio.title}</CardTitle>
                    </div>
                    <Badge className={getStatusColor(portfolio.status)}>
                      {portfolio.status.replace('_', ' ')}
                    </Badge>
                  </div>
                </CardHeader>
                <CardContent className="space-y-3">
                  {portfolio.description && (
                    <p className="text-sm text-muted-foreground line-clamp-2">
                      {portfolio.description}
                    </p>
                  )}

                  <div className="space-y-2 text-sm">
                    <div className="flex items-center gap-2">
                      <User className="h-4 w-4 text-muted-foreground" />
                      <span>{portfolio.client.name}</span>
                    </div>
                    
                    {portfolio.project && (
                      <div className="flex items-center gap-2">
                        <Folder className="h-4 w-4 text-muted-foreground" />
                        <span>{portfolio.project.title}</span>
                      </div>
                    )}

                    {portfolio.taskType && (
                      <div className="flex items-center gap-2">
                        <FileText className="h-4 w-4 text-muted-foreground" />
                        <span>{portfolio.taskType.task_type}</span>
                      </div>
                    )}

                    <div className="flex items-center gap-2">
                      <Calendar className="h-4 w-4 text-muted-foreground" />
                      <span>{formatDate(portfolio.created_at)}</span>
                    </div>
                  </div>

                  <div className="flex items-center justify-between pt-2 border-t">
                    <span className="text-sm text-muted-foreground">
                      {portfolio.file_count} file{portfolio.file_count !== 1 ? 's' : ''}
                    </span>
                    <div className="flex gap-2">
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => router.push(`/portfolio/${portfolio.id}`)}
                      >
                        <Eye className="h-4 w-4" />
                      </Button>
                      {portfolio.main_file && (
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => window.open(portfolio.main_file?.original_url, '_blank')}
                        >
                          <Download className="h-4 w-4" />
                        </Button>
                      )}
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}

        {!loading && portfolios.length === 0 && (
          <Card>
            <CardContent className="flex flex-col items-center justify-center py-12">
              <Archive className="h-12 w-12 text-muted-foreground mb-4" />
              <h3 className="text-lg font-medium mb-2">No portfolio items found</h3>
              <p className="text-muted-foreground mb-4">
                {search || Object.values(filters).some(f => f) 
                  ? 'Try adjusting your search or filters'
                  : 'Get started by adding your first portfolio item'
                }
              </p>
              <Button onClick={() => router.push('/portfolio/new')}>
                <Plus className="h-4 w-4 mr-2" />
                Add Portfolio Item
              </Button>
            </CardContent>
          </Card>
        )}
      </div>
    </MainLayout>
  );
}
