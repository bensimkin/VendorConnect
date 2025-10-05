'use client';

import React, { useState, useEffect } from 'react';
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
import { useAuthStore } from '@/lib/auth-store';
import { filterSensitiveClientDataArray, hasAdminPrivileges } from '@/lib/utils/role-utils';

// Helper function to get client display name
const getClientDisplayName = (client: { first_name?: string; last_name?: string; name?: string }) => {
  const fullName = `${client.first_name || ''} ${client.last_name || ''}`.trim();
  return fullName || client.name || 'Unnamed Client';
};

interface Client {
  id: number;
  first_name?: string;
  last_name?: string;
  name?: string;
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
  const { user } = useAuthStore();
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
      // Request more clients per page to show all clients
      params.append('per_page', '50');
      
      const response = await apiClient.get(`/clients?${params.toString()}`);
      // Handle paginated response
      const clientData = response.data.data || [];
      const rawClients = Array.isArray(clientData) ? clientData : [];
      
      // Filter sensitive data based on user role
      const filteredClients = filterSensitiveClientDataArray(rawClients, user);
      setClients(filteredClients);
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
        <div className="flex items-center justify-center min-h-screen">
          <div className="text-center">
            <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900 mx-auto"></div>
            <p className="mt-4 text-lg">Loading clients...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-between mb-8 flex-wrap gap-2">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Clients</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">
              Manage your client relationships and projects
            </p>
          </div>
          <Button
            onClick={() => router.push('/clients/new')}
            className="bg-blue-600 hover:bg-blue-700 text-white"
          >
            <Plus className="h-4 w-4 mr-2" />
            Add Client
          </Button>
        </div>

        {/* Search Bar */}
        <div className="mb-6">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
            <Input
              type="text"
              placeholder="Search clients by name, company, or email..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="pl-10"
            />
          </div>
        </div>

        {/* Clients Grid */}
        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          {filteredClients.map((client) => (
            <Card
              key={client.id}
              className="hover:shadow-lg transition-shadow cursor-pointer"
              onClick={() => router.push(`/clients/${client.id}`)}
            >
              <CardHeader>
                <div className="flex items-start justify-between">
                  <div className="flex-1 min-w-0">
                    <CardTitle className="text-xl truncate">{getClientDisplayName(client) || 'Unnamed Client'}</CardTitle>
                    {client.company && (
                      <CardDescription className="flex items-center mt-1">
                        <Building className="mr-1 h-3 w-3 flex-shrink-0" />
                        <span className="truncate">{client.company}</span>
                      </CardDescription>
                    )}
                  </div>
                  <div
                    className={`px-2 py-1 rounded-full text-xs font-medium flex-shrink-0 ml-2 ${
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
                <div className="space-y-3 text-sm">
                  {/* Only show sensitive data to admin users */}
                  {hasAdminPrivileges(user) && client.email && (
                    <div className="flex items-center text-muted-foreground">
                      <Mail className="mr-2 h-4 w-4 flex-shrink-0" />
                      <span className="truncate min-w-0">{client.email}</span>
                    </div>
                  )}
                  {hasAdminPrivileges(user) && client.phone && (
                    <div className="flex items-center text-muted-foreground">
                      <Phone className="mr-2 h-4 w-4 flex-shrink-0" />
                      <span className="truncate min-w-0">{client.phone}</span>
                    </div>
                  )}
                  {hasAdminPrivileges(user) && client.address && (
                    <div className="flex items-center text-muted-foreground">
                      <MapPin className="mr-2 h-4 w-4 flex-shrink-0" />
                      <span className="truncate min-w-0">{client.address}</span>
                    </div>
                  )}
                  {/* Show non-sensitive data to all users */}
                  <div className="flex items-center text-muted-foreground">
                    <Calendar className="mr-2 h-4 w-4 flex-shrink-0" />
                    <span className="truncate min-w-0">
                      Joined {format(new Date(client.created_at), 'MMM d, yyyy')}
                    </span>
                  </div>
                </div>

                <div className="pt-4 border-t">
                  <div className="flex items-center justify-between mb-3">
                    <div className="flex items-center space-x-3 text-sm">
                      <div className="flex items-center">
                        <Briefcase className="mr-1 h-4 w-4 text-muted-foreground flex-shrink-0" />
                        <span className="font-medium">{client.projects_count || 0}</span>
                        <span className="text-muted-foreground ml-1">projects</span>
                      </div>
                      {(client.active_projects || 0) > 0 && (
                        <div className="text-green-600 dark:text-green-400">
                          {client.active_projects} active
                        </div>
                      )}
                    </div>
                  </div>
                  
                  <div className="flex items-center justify-between">
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={(e) => {
                        e.stopPropagation();
                        handleToggleStatus(client.id, client.status === 1 ? 0 : 1, getClientDisplayName(client) || '');
                      }}
                      className={`text-xs ${
                        client.status === 1
                          ? 'text-red-600 hover:text-red-700 hover:bg-red-50'
                          : 'text-green-600 hover:text-green-700 hover:bg-green-50'
                      }`}
                    >
                      {client.status === 1 ? 'Deactivate' : 'Activate'}
                    </Button>
                    <div className="flex items-center space-x-1">
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={(e) => {
                          e.stopPropagation();
                          router.push(`/clients/${client.id}/edit`);
                        }}
                        className="p-1 h-8 w-8"
                      >
                        <Edit className="h-3 w-3" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={(e) => {
                          e.stopPropagation();
                          handleDeleteClient(client.id, getClientDisplayName(client) || '');
                        }}
                        className="text-red-600 hover:text-red-700 hover:bg-red-50 p-1 h-8 w-8"
                      >
                        <Trash2 className="h-3 w-3" />
                      </Button>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {filteredClients.length === 0 && !loading && (
          <div className="text-center py-12">
            <Building className="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-2">No clients found</h3>
            <p className="text-gray-600 dark:text-gray-400 mb-4">
              {searchTerm ? 'Try adjusting your search terms.' : 'Get started by adding your first client.'}
            </p>
            {!searchTerm && (
              <Button
                onClick={() => router.push('/clients/new')}
                className="bg-blue-600 hover:bg-blue-700 text-white"
              >
                <Plus className="h-4 w-4 mr-2" />
                Add Client
              </Button>
            )}
          </div>
        )}
      </div>
    </MainLayout>
  );
}