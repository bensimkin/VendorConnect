'use client';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { LogIn, LogOut, Clock, Users, TrendingUp, AlertCircle } from 'lucide-react';

interface SessionAnalyticsProps {
  statistics: any;
  formatDuration: (seconds: number | null) => string;
}

export default function SessionAnalytics({ statistics, formatDuration }: SessionAnalyticsProps) {
  return (
    <div className="space-y-6">
      {/* Overview Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Sessions</CardTitle>
            <LogIn className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.overview.total_sessions}</div>
            <p className="text-xs text-muted-foreground">All login sessions</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Active Users</CardTitle>
            <Users className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.overview.active_users}</div>
            <p className="text-xs text-muted-foreground">Currently logged in</p>
          </CardContent>
        </Card>

        {/* <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Avg. Session Duration</CardTitle>
            <Clock className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {formatDuration(statistics.overview.average_session_duration_seconds)}
            </div>
            <p className="text-xs text-muted-foreground">Per session</p>
          </CardContent>
        </Card> */}

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Unique Logins</CardTitle>
            <TrendingUp className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.overview.unique_users}</div>
            <p className="text-xs text-muted-foreground">Distinct users</p>
          </CardContent>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Most Active Users */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Users className="w-5 h-5" />
              Most Active Users
            </CardTitle>
          </CardHeader>
          <CardContent  className="max-h-[300px] overflow-y-auto">
            <div className="space-y-3">
              {statistics.top_users_by_sessions?.length > 0 ? (
                statistics.top_users_by_sessions.map((user: any) => (
                  <div key={user.user_id} className="flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100">
                    <div className="flex-1">
                      <p className="font-medium text-sm">{user.user_name}</p>
                      <p className="text-xs text-gray-500">{user.user_email}</p>
                    </div>
                    <div className="flex items-center gap-2">
                      <Badge variant="secondary">{user.session_count} sessions</Badge>
                      <Badge variant="outline">
                        {formatDuration(user.avg_duration_seconds)}
                      </Badge>
                    </div>
                  </div>
                ))
              ) : (
                <p className="text-center text-gray-500 py-4">No data available</p>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Recent Sessions */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <LogIn className="w-5 h-5" />
              Recent Sessions
            </CardTitle>
          </CardHeader>
          <CardContent  className="max-h-[300px] overflow-y-auto">
            <div className="space-y-3">
              {statistics.recent_sessions?.length > 0 ? (
                statistics.recent_sessions.map((session: any) => (
                  <div key={session.session_id} className="p-3 rounded-lg bg-gray-50 hover:bg-gray-100">
                    <div className="flex items-center justify-between">
                      <div className="flex-1">
                        <p className="font-medium text-sm">{session.user_name}</p>
                        <p className="text-xs text-gray-500">
                          {new Date(session.login_at).toLocaleString()}
                        </p>
                      </div>
                      {session.logout_at ? (
                        <div className="flex items-center gap-2">
                          <Badge variant="outline" className="text-xs">
                            {formatDuration(session.duration_seconds)}
                          </Badge>
                          <LogOut className="w-4 h-4 text-green-600" />
                        </div>
                      ) : (
                        <Badge variant="secondary" className="text-xs">
                          Active
                        </Badge>
                      )}
                    </div>
                  </div>
                ))
              ) : (
                <p className="text-center text-gray-500 py-4">No data available</p>
              )}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Inactive Users Alert */}
      {statistics.inactive_users?.length > 0 && (
        <Card className="border-orange-200">
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-orange-700">
              <AlertCircle className="w-5 h-5" />
              Inactive Users Alert
            </CardTitle>
            <p className="text-sm text-gray-600 mt-2">
              Users with active tasks who haven't logged in for 5+ days
            </p>
          </CardHeader>
          <CardContent  className="max-h-[300px] overflow-y-auto">
            <div className="space-y-3">
              {statistics.inactive_users.map((user: any) => (
                <div key={user.user_id} className="flex items-start justify-between p-4 rounded-lg bg-orange-50 border border-orange-200">
                  <div className="flex-1">
                    <p className="font-medium text-sm text-orange-900">{user.user_name}</p>
                    <p className="text-xs text-gray-600">{user.user_email}</p>
                    <div className="flex flex-wrap gap-2 mt-2">
                      <Badge variant="outline" className="text-xs">
                        {user.active_tasks_count} active tasks
                      </Badge>
                      <p className="text-xs text-orange-700">
                        Last login: {new Date(user.last_login_at).toLocaleString()}
                      </p>
                    </div>
                    <p className="text-xs text-orange-700 mt-1">
                      Days since last login: {user.days_since_login}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Session Activity Trend */}
      {statistics.session_activity_trend && statistics.session_activity_trend.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <TrendingUp className="w-5 h-5" />
              Session Activity Trend
            </CardTitle>
          </CardHeader>
          <CardContent  className="max-h-[300px] overflow-y-auto">
            <div className="space-y-3">
              {statistics.session_activity_trend.map((day: any) => (
                <div key={day.date} className="flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100">
                  <div className="flex-1">
                    <p className="font-medium text-sm">{day.date}</p>
                  </div>
                  <div className="flex items-center gap-4">
                    <div className="text-right">
                      <p className="text-sm font-bold">{day.active_users}</p>
                      <p className="text-xs text-gray-500">Active users</p>
                    </div>
                    <div className="text-right">
                      <p className="text-sm font-bold">{day.total_sessions}</p>
                      <p className="text-xs text-gray-500">Sessions</p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}

