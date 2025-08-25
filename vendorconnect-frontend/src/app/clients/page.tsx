'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { toast } from 'react-hot-toast';
import RoleGuard from '@/components/auth/role-guard';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import apiClient from '@/lib/api-client';
import { Plus, Search, Building, Mail, Phone, MapPin, Calendar, Briefcase, Edit, Trash2 } from 'lucide-react';
import { format } from 'date-fns';

interface Client {
  id: number;
  name: string;
  email?: string;
  phone?: string;
  address?: string;
  company?: string;
  status?: number;
  created_at: string;
  projects_count?: number;
  active_projects?: number;
}

export default function ClientsPage() {
  const router = useRouter();
  const [clients, setClients] = useState<Client[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    fetchClients();
  }, []);

  // Debounced search effect
  useEffect(() => {
    const timeoutId = setTimeout(() => {
      fetchClients(searchTerm);
    }, 500); // 500ms delay

    return () => clearTimeout(timeoutId);
  }, [searchTerm]);

  const fetchClients = async (searchQuery = '') => {
    try {
      const params = new URLSearchParams();
      if (searchQuery) {
        params.append('search', searchQuery);
      }
      
      const response = await apiClient.get(`/clients?${params.toString()}`);
      // Handle paginated response
      const clientData = response.data.data?.data || response.data.data || [];
      setClients(Array.isArray(clientData) ? clientData : []);
    } catch (error) {
      console.error('Failed to fetch clients:', error);
      setClients([]);
    } finally {
      setLoading(false);
    }
  };

  // Use clients directly since search is now server-side
  const filteredClients = clients;

  const handleDeleteClient = async (clientId: number, clientName: string) => {
    if (!confirm(`Are you sure you want to delete "${clientName}"? This action cannot be undone.`)) {
      return;
    }

    try {
      await apiClient.delete(`/clients/${clientId}`);
      toast.success('Client deleted successfully');
      fetchClients(); // Refresh the list
    } catch (error: any) {
      console.error('Failed to delete client:', error);
      toast.error('Failed to delete client');
    }
  };

  const handleToggleStatus = async (clientId: number, newStatus: number, clientName: string) => {
    const action = newStatus === 1 ? 'activate' : 'deactivate';
    if (!confirm(`Are you sure you want to ${action} "${clientName}"?`)) {
      return;
    }

    try {
      await apiClient.put(`/clients/${clientId}`, { status: newStatus });
      toast.success(`Client ${action}d successfully`);
      fetchClients(); // Refresh the list
    } catch (error: any) {
      console.error(`Failed to ${action} client:`, error);
      toast.error(`Failed to ${action} client`);
    }
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading clients...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <RoleGuard allowedRoles={['Admin']}>
      <MainLayout>
        <div className="space-y-8">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Clients</h1>
            <p className="text-muted-foreground">Manage your client relationships and projects</p>
          </div>
          <Button onClick={() => router.push('/clients/new')}>
            <Plus className="mr-2 h-4 w-4" />
            New Client
          </Button>
        </div>

        {/* Search */}
        <Card>
          <CardContent className="p-4">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
              <Input
                placeholder="Search clients by name, email, or company..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10"
              />
            </div>
          </CardContent>
        </Card>

        {/* Clients Grid */}
        {filteredClients.length > 0 ? (
          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {filteredClients.map((client) => (
              <Card key={client.id} className="hover:shadow-lg transition-shadow cursor-pointer" onClick={() => router.push(`/clients/${client.id}`)}>
                <CardHeader>
                  <div className="flex items-start justify-between">
                    <div>
                      <CardTitle className="text-xl">{client.name || 'Unnamed Client'}</CardTitle>
                      {client.company && (
                        <CardDescription className="flex items-center mt-1">
                          <Building className="mr-1 h-3 w-3" />
                          {client.company}
                        </CardDescription>
                      )}
                    </div>
                    <div
                      className={`px-2 py-1 rounded-full text-xs font-medium ${
                        client.status === 1
                          ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                          : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                      }`}
                    >
                      {client.status === 1 ? 'Active' : 'Inactive'}
                    </div>
                  </div>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="space-y-2 text-sm">
                    {client.email && (
                      <div className="flex items-center text-muted-foreground">
                        <Mail className="mr-2 h-4 w-4 flex-shrink-0" />
                        <span className="truncate">{client.email}</span>
                      </div>
                    )}
                    {client.phone && (
                      <div className="flex items-center text-muted-foreground">
                        <Phone className="mr-2 h-4 w-4 flex-shrink-0" />
                        <span>{client.phone}</span>
                      </div>
                    )}
                    {client.address && (
                      <div className="flex items-center text-muted-foreground">
                        <MapPin className="mr-2 h-4 w-4 flex-shrink-0" />
                        <span className="truncate">{client.address}</span>
                      </div>
                    )}
                  </div>

                  <div className="flex items-center justify-between pt-4 border-t">
                    <div className="flex items-center space-x-4 text-sm">
                      <div className="flex items-center">
                        <Briefcase className="mr-1 h-4 w-4 text-muted-foreground" />
                        <span className="font-medium">{client.projects_count || 0}</span>
                        <span className="text-muted-foreground ml-1">projects</span>
                      </div>
                      {(client.active_projects || 0) > 0 && (
                        <div className="text-green-600 dark:text-green-400">
                          {client.active_projects} active
                        </div>
                      )}
                    </div>
                    <div className="flex items-center space-x-2">
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={(e) => {
                          e.stopPropagation();
                          handleToggleStatus(client.id, client.status === 1 ? 0 : 1, client.name);
                        }}
                        className={client.status === 1 ? 'text-orange-600 hover:text-orange-700 hover:bg-orange-50' : 'text-green-600 hover:text-green-700 hover:bg-green-50'}
                        title={client.status === 1 ? 'Deactivate Client' : 'Activate Client'}
                      >
                        {client.status === 1 ? 'Deactivate' : 'Activate'}
                      </Button>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={(e) => {
                          e.stopPropagation();
                          router.push(`/clients/${client.id}/edit`);
                        }}
                      >
                        <Edit className="h-3 w-3" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={(e) => {
                          e.stopPropagation();
                          handleDeleteClient(client.id, client.name);
                        }}
                        className="text-red-600 hover:text-red-700 hover:bg-red-50"
                      >
                        <Trash2 className="h-3 w-3" />
                      </Button>
                    </div>
                  </div>

                  <div className="flex items-center text-xs text-muted-foreground">
                    <Calendar className="mr-1 h-3 w-3" />
                    Added {format(new Date(client.created_at), 'MMM dd, yyyy')}
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <div className="text-center py-12">
            <p className="text-muted-foreground">
              {searchTerm ? 'No clients found matching your search.' : 'No clients available. Create your first client to get started!'}
            </p>
          </div>
        )}
      </div>
      </MainLayout>
    </RoleGuard>
  );
}