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
  const [companyAnalytics, setCompanyAnalytics] = useState<any>(null);
  const [analyticsLoading, setAnalyticsLoading] = useState(false);

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

  const fetchCompanyAnalytics = async (adminId: number) => {
    try {
      setAnalyticsLoading(true);
      const response = await apiClient.get(`/owner/companies/${adminId}/analytics`);
      setCompanyAnalytics(response.data.data);
    } catch (error: any) {
      console.error('Failed to fetch company analytics:', error);
      toast.error('Failed to load company analytics');
    } finally {
      setAnalyticsLoading(false);
    }
  };

  const handleViewAnalytics = (companyId: number) => {
    setSelectedCompany(companyId);
    fetchCompanyAnalytics(companyId);
  };

  const closeAnalytics = () => {
    setSelectedCompany(null);
    setCompanyAnalytics(null);
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
                          onClick={() => handleViewAnalytics(company.id)}
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

      {/* Company Analytics Modal */}
      {selectedCompany && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50" onClick={closeAnalytics}>
          <Card className="max-w-4xl w-full max-h-[80vh] overflow-y-auto" onClick={(e) => e.stopPropagation()}>
            <CardHeader>
              <CardTitle>
                Company Analytics: {companies.find(c => c.id === selectedCompany)?.company_name}
              </CardTitle>
              <Button
                size="sm"
                variant="ghost"
                className="absolute top-4 right-4"
                onClick={closeAnalytics}
              >
                ✕
              </Button>
            </CardHeader>
            <CardContent>
              {analyticsLoading ? (
                <div className="flex items-center justify-center py-12">
                  <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                </div>
              ) : companyAnalytics ? (
                <div className="space-y-6">
                  {/* Overview Stats */}
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div className="text-center p-4 bg-green-50 rounded-lg">
                      <div className="text-3xl font-bold text-green-600">{companyAnalytics.overview.total_projects}</div>
                      <div className="text-sm text-muted-foreground">Projects</div>
                    </div>
                    <div className="text-center p-4 bg-purple-50 rounded-lg">
                      <div className="text-3xl font-bold text-purple-600">{companyAnalytics.overview.total_tasks}</div>
                      <div className="text-sm text-muted-foreground">Tasks</div>
                    </div>
                    <div className="text-center p-4 bg-orange-50 rounded-lg">
                      <div className="text-3xl font-bold text-orange-600">{companyAnalytics.overview.total_clients}</div>
                      <div className="text-sm text-muted-foreground">Clients</div>
                    </div>
                    <div className="text-center p-4 bg-blue-50 rounded-lg">
                      <div className="text-3xl font-bold text-blue-600">{companyAnalytics.overview.total_users}</div>
                      <div className="text-sm text-muted-foreground">Users</div>
                    </div>
                  </div>

                  {/* Recent Activity */}
                  <div>
                    <h3 className="font-semibold mb-2">Recent Activity ({companyAnalytics.period_activity.start_date} to {companyAnalytics.period_activity.end_date})</h3>
                    <div className="grid grid-cols-2 gap-4">
                      <div className="p-3 bg-gray-50 rounded">
                        <div className="text-lg font-semibold">{companyAnalytics.period_activity.projects_created}</div>
                        <div className="text-sm text-muted-foreground">Projects Created</div>
                      </div>
                      <div className="p-3 bg-gray-50 rounded">
                        <div className="text-lg font-semibold">{companyAnalytics.period_activity.tasks_created}</div>
                        <div className="text-sm text-muted-foreground">Tasks Created</div>
                      </div>
                    </div>
                  </div>

                  {/* Tasks by Status */}
                  <div>
                    <h3 className="font-semibold mb-2">Tasks by Status</h3>
                    <div className="space-y-2">
                      {companyAnalytics.tasks_by_status.map((item: any, index: number) => (
                        <div key={index} className="flex items-center justify-between p-2 bg-gray-50 rounded">
                          <span className="text-sm">{item.status}</span>
                          <Badge variant="secondary">{item.count}</Badge>
                        </div>
                      ))}
                    </div>
                  </div>

                  {/* Activity Metrics */}
                  <div>
                    <h3 className="font-semibold mb-3">🔥 Active Usage</h3>
                    <div className="grid grid-cols-3 gap-4">
                      <div className="p-4 bg-green-50 rounded-lg border-2 border-green-200">
                        <div className="text-2xl font-bold text-green-600">{companyAnalytics.activity_metrics.active_users_today}</div>
                        <div className="text-xs text-muted-foreground">Active Today</div>
                      </div>
                      <div className="p-4 bg-blue-50 rounded-lg">
                        <div className="text-2xl font-bold text-blue-600">{companyAnalytics.activity_metrics.active_users_7_days}</div>
                        <div className="text-xs text-muted-foreground">Last 7 Days</div>
                      </div>
                      <div className="p-4 bg-purple-50 rounded-lg">
                        <div className="text-2xl font-bold text-purple-600">{companyAnalytics.activity_metrics.active_users_30_days}</div>
                        <div className="text-xs text-muted-foreground">Last 30 Days</div>
                      </div>
                    </div>
                  </div>

                  {/* Session Stats */}
                  <div className="grid grid-cols-2 gap-4">
                    <div className="p-4 bg-gray-50 rounded-lg">
                      <div className="text-sm text-muted-foreground mb-1">Sessions (Last 7 Days)</div>
                      <div className="text-xl font-bold">{companyAnalytics.activity_metrics.total_sessions_7_days}</div>
                    </div>
                    <div className="p-4 bg-gray-50 rounded-lg">
                      <div className="text-sm text-muted-foreground mb-1">Avg Session Duration</div>
                      <div className="text-xl font-bold">
                        {Math.floor(companyAnalytics.activity_metrics.avg_session_duration_seconds / 60)}m
                      </div>
                    </div>
                  </div>

                  {/* Last Activity */}
                  {companyAnalytics.activity_metrics.last_activity_at && (
                    <div className="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                      <div className="text-sm text-muted-foreground mb-1">Last Activity</div>
                      <div className="font-semibold">
                        {new Date(companyAnalytics.activity_metrics.last_activity_at).toLocaleString()}
                      </div>
                      <div className="text-xs text-muted-foreground mt-1">
                        {Math.floor((new Date().getTime() - new Date(companyAnalytics.activity_metrics.last_activity_at).getTime()) / (1000 * 60))} minutes ago
                      </div>
                    </div>
                  )}
                </div>
              ) : (
                <p className="text-muted-foreground">No analytics data available</p>
              )}
            </CardContent>
          </Card>
        </div>
      )}
    </div>
  );
}

