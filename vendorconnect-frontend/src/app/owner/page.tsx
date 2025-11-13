'use client';

import { useEffect, useState } from 'react';
import { apiClient } from '@/lib/api-client';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Building2, Users, FolderOpen, CheckSquare, User as UserIcon, Mail, Calendar, TrendingUp } from 'lucide-react';
import { toast } from 'react-hot-toast';
import Link from 'next/link';

interface Company {
  id: number;
  company_name: string;
  company_email: string;
  owner: {
    id: number;
    name: string;
    email: string;
  };
  created_at: string;
  stats: {
    projects: number;
    tasks: number;
    clients: number;
    users: number;
  };
}

interface PlatformStats {
  total_companies: number;
  total_users: number;
  total_projects: number;
  total_tasks: number;
  total_clients: number;
}

export default function OwnerDashboard() {
  const [companies, setCompanies] = useState<Company[]>([]);
  const [platformStats, setPlatformStats] = useState<PlatformStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedCompany, setSelectedCompany] = useState<number | null>(null);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      const [companiesResponse, statsResponse] = await Promise.all([
        apiClient.get('/owner/companies'),
        apiClient.get('/owner/platform-stats'),
      ]);
      
      setCompanies(companiesResponse.data.data);
      setPlatformStats(statsResponse.data.data.overview);
    } catch (error: any) {
      console.error('Failed to fetch owner data:', error);
      toast.error(error.response?.data?.message || 'Failed to load owner dashboard');
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
          <p className="text-muted-foreground">Loading owner dashboard...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto p-6 space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold">Platform Owner Dashboard</h1>
          <p className="text-muted-foreground">Manage and monitor all companies on VendorConnect</p>
        </div>
        <Badge variant="outline" className="px-3 py-1">
          <UserIcon className="h-3 w-3 mr-1" />
          Owner Access
        </Badge>
      </div>

      {/* Platform Stats */}
      {platformStats && (
        <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="text-sm font-medium text-muted-foreground">Companies</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex items-center gap-2">
                <Building2 className="h-8 w-8 text-primary" />
                <div className="text-3xl font-bold">{platformStats.total_companies}</div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="text-sm font-medium text-muted-foreground">Total Users</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex items-center gap-2">
                <Users className="h-8 w-8 text-blue-500" />
                <div className="text-3xl font-bold">{platformStats.total_users}</div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="text-sm font-medium text-muted-foreground">Total Projects</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex items-center gap-2">
                <FolderOpen className="h-8 w-8 text-green-500" />
                <div className="text-3xl font-bold">{platformStats.total_projects}</div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="text-sm font-medium text-muted-foreground">Total Tasks</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex items-center gap-2">
                <CheckSquare className="h-8 w-8 text-purple-500" />
                <div className="text-3xl font-bold">{platformStats.total_tasks}</div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="text-sm font-medium text-muted-foreground">Total Clients</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex items-center gap-2">
                <UserIcon className="h-8 w-8 text-orange-500" />
                <div className="text-3xl font-bold">{platformStats.total_clients}</div>
              </div>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Companies List */}
      <Card>
        <CardHeader>
          <CardTitle>All Companies</CardTitle>
          <CardDescription>Overview of all registered companies and their activity</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {companies.length === 0 ? (
              <p className="text-center text-muted-foreground py-8">No companies registered yet</p>
            ) : (
              companies.map((company) => (
                <Card key={company.id} className="hover:shadow-md transition-shadow">
                  <CardContent className="p-6">
                    <div className="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                      {/* Company Info */}
                      <div className="md:col-span-4">
                        <div className="flex items-start gap-3">
                          <div className="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <Building2 className="h-5 w-5 text-primary" />
                          </div>
                          <div>
                            <h3 className="font-semibold text-lg">{company.company_name}</h3>
                            {company.company_email && (
                              <p className="text-sm text-muted-foreground flex items-center gap-1">
                                <Mail className="h-3 w-3" />
                                {company.company_email}
                              </p>
                            )}
                            <p className="text-xs text-muted-foreground flex items-center gap-1 mt-1">
                              <UserIcon className="h-3 w-3" />
                              Owner: {company.owner.name}
                            </p>
                          </div>
                        </div>
                      </div>

                      {/* Stats */}
                      <div className="md:col-span-6 grid grid-cols-4 gap-4 text-center">
                        <div>
                          <div className="text-2xl font-bold text-green-600">{company.stats.projects}</div>
                          <div className="text-xs text-muted-foreground">Projects</div>
                        </div>
                        <div>
                          <div className="text-2xl font-bold text-purple-600">{company.stats.tasks}</div>
                          <div className="text-xs text-muted-foreground">Tasks</div>
                        </div>
                        <div>
                          <div className="text-2xl font-bold text-orange-600">{company.stats.clients}</div>
                          <div className="text-xs text-muted-foreground">Clients</div>
                        </div>
                        <div>
                          <div className="text-2xl font-bold text-blue-600">{company.stats.users}</div>
                          <div className="text-xs text-muted-foreground">Users</div>
                        </div>
                      </div>

                      {/* Actions */}
                      <div className="md:col-span-2 flex flex-col gap-2">
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => setSelectedCompany(company.id)}
                          className="w-full"
                        >
                          <TrendingUp className="h-4 w-4 mr-2" />
                          View Analytics
                        </Button>
                        <div className="text-xs text-muted-foreground text-center">
                          <Calendar className="h-3 w-3 inline mr-1" />
                          Joined {new Date(company.created_at).toLocaleDateString()}
                        </div>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))
            )}
          </div>
        </CardContent>
      </Card>

      {/* Company Analytics Modal (simplified - you can expand this) */}
      {selectedCompany && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <Card className="max-w-2xl w-full max-h-[80vh] overflow-y-auto">
            <CardHeader>
              <CardTitle>
                Company Analytics: {companies.find(c => c.id === selectedCompany)?.company_name}
              </CardTitle>
              <Button
                size="sm"
                variant="ghost"
                className="absolute top-4 right-4"
                onClick={() => setSelectedCompany(null)}
              >
                ✕
              </Button>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground">
                Detailed analytics coming soon. Use /owner/companies/{selectedCompany}/analytics API endpoint for data.
              </p>
            </CardContent>
          </Card>
        </div>
      )}
    </div>
  );
}

