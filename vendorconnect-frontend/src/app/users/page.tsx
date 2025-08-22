'use client';

import { useState, useEffect } from 'react';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import apiClient from '@/lib/api-client';
import { Plus, Search, Mail, Phone, Shield, Edit, Trash2, UserCheck, UserX } from 'lucide-react';
import { format } from 'date-fns';

interface User {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  status: number;
  created_at: string;
  roles: { id: number; name: string }[];
  photo: string | null;
}

export default function UsersPage() {
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    fetchUsers();
  }, []);

  const fetchUsers = async () => {
    try {
      const response = await apiClient.get('/users');
      setUsers(response.data.data);
    } catch (error) {
      console.error('Failed to fetch users:', error);
    } finally {
      setLoading(false);
    }
  };

  const filteredUsers = users.filter(user => {
    const fullName = `${user.first_name} ${user.last_name}`.toLowerCase();
    const email = user.email.toLowerCase();
    const search = searchTerm.toLowerCase();
    return fullName.includes(search) || email.includes(search);
  });

  if (loading) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading users...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="space-y-8">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Users</h1>
            <p className="text-muted-foreground">Manage system users and their permissions</p>
          </div>
          <Button>
            <Plus className="mr-2 h-4 w-4" />
            New User
          </Button>
        </div>

        {/* Search */}
        <Card>
          <CardContent className="p-4">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
              <Input
                placeholder="Search users by name or email..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10"
              />
            </div>
          </CardContent>
        </Card>

        {/* Users Grid */}
        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          {filteredUsers.map((user) => (
            <Card key={user.id} className="overflow-hidden">
              <CardHeader className="pb-4">
                <div className="flex items-start justify-between">
                  <div className="flex items-center space-x-4">
                    <div className="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                      {user.photo ? (
                        <img
                          src={user.photo}
                          alt={`${user.first_name} ${user.last_name}`}
                          className="w-12 h-12 rounded-full object-cover"
                        />
                      ) : (
                        <span className="text-lg font-semibold text-primary">
                          {user.first_name[0]}{user.last_name[0]}
                        </span>
                      )}
                    </div>
                    <div>
                      <CardTitle className="text-lg">
                        {user.first_name} {user.last_name}
                      </CardTitle>
                      <CardDescription>{user.email}</CardDescription>
                    </div>
                  </div>
                  <div className="flex items-center space-x-1">
                    <Button variant="ghost" size="icon" className="h-8 w-8">
                      <Edit className="h-4 w-4" />
                    </Button>
                    <Button variant="ghost" size="icon" className="h-8 w-8">
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2 text-sm">
                  {user.phone && (
                    <div className="flex items-center text-muted-foreground">
                      <Phone className="mr-2 h-4 w-4" />
                      <span>{user.phone}</span>
                    </div>
                  )}
                  <div className="flex items-center text-muted-foreground">
                    <Mail className="mr-2 h-4 w-4" />
                    <span className="truncate">{user.email}</span>
                  </div>
                </div>

                <div className="flex items-center justify-between pt-4 border-t">
                  <div className="flex items-center space-x-2">
                    <Shield className="h-4 w-4 text-muted-foreground" />
                    <div className="flex flex-wrap gap-1">
                      {user.roles.map((role) => (
                        <span
                          key={role.id}
                          className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary"
                        >
                          {role.name}
                        </span>
                      ))}
                    </div>
                  </div>
                  <div className="flex items-center">
                    {user.status === 1 ? (
                      <span className="flex items-center text-green-600 text-xs">
                        <UserCheck className="mr-1 h-3 w-3" />
                        Active
                      </span>
                    ) : (
                      <span className="flex items-center text-red-600 text-xs">
                        <UserX className="mr-1 h-3 w-3" />
                        Inactive
                      </span>
                    )}
                  </div>
                </div>

                <div className="text-xs text-muted-foreground">
                  Joined {format(new Date(user.created_at), 'MMM dd, yyyy')}
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {filteredUsers.length === 0 && (
          <div className="text-center py-12">
            <p className="text-muted-foreground">No users found matching your search.</p>
          </div>
        )}
      </div>
    </MainLayout>
  );
}
