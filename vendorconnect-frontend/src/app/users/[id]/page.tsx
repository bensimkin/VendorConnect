'use client';

import { useState, useEffect } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { toast } from 'react-hot-toast';
import RoleGuard from '@/components/auth/role-guard';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, Mail, Phone, Calendar, User, Building, MapPin, Trash2 } from 'lucide-react';
import { apiClient } from '@/lib/api-client';
import { format } from 'date-fns';

interface User {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  phone?: string;
  status: number;
  created_at: string;
  updated_at: string;
  roles?: Array<{ id: number; name: string }>;
  permissions?: Array<{ id: number; name: string }>;
}

export default function UserDetailPage() {
  const router = useRouter();
  const params = useParams();
  const userId = params.id as string;

  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (userId) {
      fetchUser();
    }
  }, [userId]);

  const fetchUser = async () => {
    try {
      const response = await apiClient.get(`/users/${userId}`);
      setUser(response.data.data);
    } catch (error: any) {
      console.error('Failed to fetch user:', error);
      toast.error('Failed to load user details');
      router.push('/users');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async () => {
    if (!user) return;

    if (!confirm(`Are you sure you want to delete "${user.first_name} ${user.last_name}"? This action cannot be undone.`)) {
      return;
    }

    try {
      await apiClient.delete(`/users/${userId}`);
      toast.success('User deleted successfully');
      router.push('/users');
    } catch (error: any) {
      console.error('Failed to delete user:', error);
      toast.error(error.response?.data?.message || 'Failed to delete user');
    }
  };

  const getStatusBadge = (status: number) => {
    return status === 1 ? (
      <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
        Active
      </Badge>
    ) : (
      <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
        Inactive
      </Badge>
    );
  };

  const formatDate = (dateString: string) => {
    try {
      return format(new Date(dateString), 'MMM dd, yyyy');
    } catch {
      return 'Invalid date';
    }
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading user details...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  if (!user) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <p className="text-muted-foreground">User not found</p>
            <Button onClick={() => router.push('/users')} className="mt-4">
              Back to Users
            </Button>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <RoleGuard allowedRoles={['admin']}>
      <MainLayout>
        <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center gap-4">
          <Button
            variant="ghost"
            size="icon"
            onClick={() => router.push('/users')}
          >
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div className="flex-1">
            <h1 className="text-2xl font-bold">{user.first_name} {user.last_name}</h1>
            <p className="text-muted-foreground">User Profile</p>
          </div>
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              onClick={() => router.push(`/users/${userId}/edit`)}
            >
              <Edit className="mr-2 h-4 w-4" />
              Edit User
            </Button>
            <Button
              variant="destructive"
              onClick={handleDelete}
            >
              <Trash2 className="mr-2 h-4 w-4" />
              Delete
            </Button>
          </div>
        </div>

        <div className="grid gap-6 md:grid-cols-2">
          {/* Basic Information */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <User className="h-5 w-5" />
                Basic Information
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Full Name</span>
                <span className="text-sm">{user.first_name} {user.last_name}</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Email</span>
                <span className="text-sm flex items-center gap-2">
                  <Mail className="h-4 w-4" />
                  {user.email}
                </span>
              </div>
              {user.phone && (
                <div className="flex items-center justify-between">
                  <span className="text-sm font-medium text-muted-foreground">Phone</span>
                  <span className="text-sm flex items-center gap-2">
                    <Phone className="h-4 w-4" />
                    {user.phone}
                  </span>
                </div>
              )}
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Status</span>
                {getStatusBadge(user.status)}
              </div>
            </CardContent>
          </Card>

          {/* Account Information */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Calendar className="h-5 w-5" />
                Account Information
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Member Since</span>
                <span className="text-sm">{formatDate(user.created_at)}</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Last Updated</span>
                <span className="text-sm">{formatDate(user.updated_at)}</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">User ID</span>
                <span className="text-sm font-mono">#{user.id}</span>
              </div>
            </CardContent>
          </Card>

          {/* Roles and Permissions */}
          <Card className="md:col-span-2">
            <CardHeader>
              <CardTitle>Roles & Permissions</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {user.roles && user.roles.length > 0 ? (
                <div>
                  <h4 className="text-sm font-medium mb-2">Roles</h4>
                  <div className="flex flex-wrap gap-2">
                    {user.roles.map((role) => (
                      <Badge key={role.id} variant="secondary">
                        {role.name}
                      </Badge>
                    ))}
                  </div>
                </div>
              ) : (
                <p className="text-sm text-muted-foreground">No roles assigned</p>
              )}

              {user.permissions && user.permissions.length > 0 && (
                <div>
                  <h4 className="text-sm font-medium mb-2">Permissions</h4>
                  <div className="flex flex-wrap gap-2">
                    {user.permissions.map((permission) => (
                      <Badge key={permission.id} variant="outline">
                        {permission.name}
                      </Badge>
                    ))}
                  </div>
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
      </MainLayout>
    </RoleGuard>
  );
}
