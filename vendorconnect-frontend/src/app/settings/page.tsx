'use client';

import { useState, useEffect } from 'react';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { useAuthStore } from '@/lib/auth-store';
import { useTheme } from 'next-themes';
import apiClient from '@/lib/api-client';
import { toast } from 'react-hot-toast';
import RoleGuard from '@/components/auth/role-guard';
import ApiKeyManager from '@/components/settings/ApiKeyManager';
import { User, Bell, Shield, Palette, Database, HelpCircle, Moon, Sun, Monitor, Key } from 'lucide-react';

export default function SettingsPage() {
  const { user } = useAuthStore();
  const { theme, setTheme } = useTheme();
  const [saving, setSaving] = useState(false);
  const [profileData, setProfileData] = useState({
    first_name: user?.first_name || '',
    last_name: user?.last_name || '',
    email: user?.email || '',
  });
  const [passwordData, setPasswordData] = useState({
    current_password: '',
    new_password: '',
    confirm_password: '',
  });
  const [notifications, setNotifications] = useState({
    email_notifications: true,
    push_notifications: false,
    task_assignments: true,
    task_updates: true,
    project_updates: true,
    mentions: true,
  });

  // Load notification preferences from user object on mount
  useEffect(() => {
    if (user?.notification_preferences) {
      setNotifications({
        email_notifications: user.notification_preferences.email_notifications ?? true,
        push_notifications: user.notification_preferences.push_notifications ?? false,
        task_assignments: user.notification_preferences.task_assignments ?? true,
        task_updates: user.notification_preferences.task_updates ?? true,
        project_updates: user.notification_preferences.project_updates ?? true,
        mentions: user.notification_preferences.mentions ?? true,
      });
    }
  }, [user]);

  // Profile update handler
  const handleProfileUpdate = async () => {
    setSaving(true);
    try {
      await apiClient.put('/user/profile', profileData);
      toast.success('Profile updated successfully');
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to update profile');
    } finally {
      setSaving(false);
    }
  };

  // Password change handler
  const handlePasswordChange = async () => {
    if (passwordData.new_password !== passwordData.confirm_password) {
      toast.error('Passwords do not match');
      return;
    }

    if (passwordData.new_password.length < 6) {
      toast.error('Password must be at least 6 characters');
      return;
    }

    setSaving(true);
    try {
      await apiClient.post('/user/change-password', {
        current_password: passwordData.current_password,
        new_password: passwordData.new_password,
      });
      toast.success('Password changed successfully');
      setPasswordData({
        current_password: '',
        new_password: '',
        confirm_password: '',
      });
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to change password');
    } finally {
      setSaving(false);
    }
  };

  // Notification settings handler
  const handleNotificationUpdate = async () => {
    setSaving(true);
    try {
      await apiClient.put('/user/notifications', notifications);
      toast.success('Notification preferences updated');
    } catch (error: any) {
      toast.error('Failed to update notification preferences');
    } finally {
      setSaving(false);
    }
  };

  const getThemeIcon = () => {
    switch (theme) {
      case 'dark':
        return <Moon className="h-4 w-4" />;
      case 'light':
        return <Sun className="h-4 w-4" />;
      default:
        return <Monitor className="h-4 w-4" />;
    }
  };

  return (
            <RoleGuard allowedRoles={['admin']}>
      <MainLayout>
        <div className="space-y-8">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Settings</h1>
          <p className="text-muted-foreground">Manage your account settings and preferences</p>
        </div>

        <div className="grid gap-6">
          {/* Profile Settings */}
          <Card>
            <CardHeader>
              <div className="flex items-center space-x-2">
                <User className="h-5 w-5" />
                <CardTitle>Profile Information</CardTitle>
              </div>
              <CardDescription>Update your personal information</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                  <Label htmlFor="firstName">First Name</Label>
                  <Input 
                    id="firstName" 
                    value={profileData.first_name}
                    onChange={(e) => setProfileData({ ...profileData, first_name: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="lastName">Last Name</Label>
                  <Input 
                    id="lastName" 
                    value={profileData.last_name}
                    onChange={(e) => setProfileData({ ...profileData, last_name: e.target.value })}
                  />
                </div>
              </div>
              <div className="space-y-2">
                <Label htmlFor="email">Email</Label>
                <Input 
                  id="email" 
                  type="email" 
                  value={profileData.email}
                  onChange={(e) => setProfileData({ ...profileData, email: e.target.value })}
                />
              </div>
              <Button onClick={handleProfileUpdate} disabled={saving}>
                {saving ? 'Saving...' : 'Save Changes'}
              </Button>
            </CardContent>
          </Card>

          {/* Notification Settings */}
          <Card>
            <CardHeader>
              <div className="flex items-center space-x-2">
                <Bell className="h-5 w-5" />
                <CardTitle>Notifications</CardTitle>
              </div>
              <CardDescription>Configure how you receive notifications</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <div className="space-y-0.5">
                    <Label htmlFor="email-notifications">Email Notifications</Label>
                    <p className="text-sm text-muted-foreground">Receive notifications via email</p>
                  </div>
                  <Switch
                    id="email-notifications"
                    checked={notifications.email_notifications}
                    onCheckedChange={(checked) => 
                      setNotifications({ ...notifications, email_notifications: checked })
                    }
                  />
                </div>
                
                <div className="flex items-center justify-between">
                  <div className="space-y-0.5">
                    <Label htmlFor="push-notifications">Push Notifications</Label>
                    <p className="text-sm text-muted-foreground">Receive browser push notifications</p>
                  </div>
                  <Switch
                    id="push-notifications"
                    checked={notifications.push_notifications}
                    onCheckedChange={(checked) => 
                      setNotifications({ ...notifications, push_notifications: checked })
                    }
                  />
                </div>

                <div className="border-t pt-4 space-y-4">
                  <p className="text-sm font-medium">Notification Types</p>
                  
                  <div className="flex items-center justify-between">
                    <Label htmlFor="task-assignments" className="font-normal">Task Assignments</Label>
                    <Switch
                      id="task-assignments"
                      checked={notifications.task_assignments}
                      onCheckedChange={(checked) => 
                        setNotifications({ ...notifications, task_assignments: checked })
                      }
                    />
                  </div>

                  <div className="flex items-center justify-between">
                    <Label htmlFor="task-updates" className="font-normal">Task Updates</Label>
                    <Switch
                      id="task-updates"
                      checked={notifications.task_updates}
                      onCheckedChange={(checked) => 
                        setNotifications({ ...notifications, task_updates: checked })
                      }
                    />
                  </div>

                  <div className="flex items-center justify-between">
                    <Label htmlFor="mentions" className="font-normal">Mentions</Label>
                    <Switch
                      id="mentions"
                      checked={notifications.mentions}
                      onCheckedChange={(checked) => 
                        setNotifications({ ...notifications, mentions: checked })
                      }
                    />
                  </div>
                </div>
              </div>
              
              <Button onClick={handleNotificationUpdate} disabled={saving}>
                {saving ? 'Saving...' : 'Save Preferences'}
              </Button>
            </CardContent>
          </Card>

          {/* Security Settings */}
          <Card>
            <CardHeader>
              <div className="flex items-center space-x-2">
                <Shield className="h-5 w-5" />
                <CardTitle>Security</CardTitle>
              </div>
              <CardDescription>Manage your password and security preferences</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="current-password">Current Password</Label>
                  <Input
                    id="current-password"
                    type="password"
                    value={passwordData.current_password}
                    onChange={(e) => 
                      setPasswordData({ ...passwordData, current_password: e.target.value })
                    }
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="new-password">New Password</Label>
                  <Input
                    id="new-password"
                    type="password"
                    value={passwordData.new_password}
                    onChange={(e) => 
                      setPasswordData({ ...passwordData, new_password: e.target.value })
                    }
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="confirm-password">Confirm New Password</Label>
                  <Input
                    id="confirm-password"
                    type="password"
                    value={passwordData.confirm_password}
                    onChange={(e) => 
                      setPasswordData({ ...passwordData, confirm_password: e.target.value })
                    }
                  />
                </div>
                <Button onClick={handlePasswordChange} disabled={saving}>
                  {saving ? 'Changing...' : 'Change Password'}
                </Button>
              </div>
            </CardContent>
          </Card>

          {/* Appearance Settings */}
          <Card>
            <CardHeader>
              <div className="flex items-center space-x-2">
                <Palette className="h-5 w-5" />
                <CardTitle>Appearance</CardTitle>
              </div>
              <CardDescription>Customize the look and feel of the application</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-4">
                <div>
                  <Label className="text-base">Theme</Label>
                  <p className="text-sm text-muted-foreground mb-3">Choose your preferred theme</p>
                  <div className="grid grid-cols-3 gap-2">
                    <Button
                      variant={theme === 'light' ? 'default' : 'outline'}
                      size="sm"
                      onClick={() => setTheme('light')}
                      className="w-full"
                    >
                      <Sun className="h-4 w-4 mr-2" />
                      Light
                    </Button>
                    <Button
                      variant={theme === 'dark' ? 'default' : 'outline'}
                      size="sm"
                      onClick={() => setTheme('dark')}
                      className="w-full"
                    >
                      <Moon className="h-4 w-4 mr-2" />
                      Dark
                    </Button>
                    <Button
                      variant={theme === 'system' ? 'default' : 'outline'}
                      size="sm"
                      onClick={() => setTheme('system')}
                      className="w-full"
                    >
                      <Monitor className="h-4 w-4 mr-2" />
                      System
                    </Button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          {
            user?.roles?.some(role => role.name.toLowerCase() === 'admin') && (
              <>
                {/* API Keys */}
                <ApiKeyManager />
              </>
            )
          }
          
          {/* Help & Support */}
          <Card>
            <CardHeader>
              <div className="flex items-center space-x-2">
                <HelpCircle className="h-5 w-5" />
                <CardTitle>Help & Support</CardTitle>
              </div>
              <CardDescription>Get help and contact support</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="font-medium">Version</p>
                    <p className="text-sm text-muted-foreground">VendorConnect v1.0.0</p>
                  </div>
                </div>
                <div className="flex items-center justify-between">
                  <div>
                    <p className="font-medium">Contact Support</p>
                    <p className="text-sm text-muted-foreground">Get help from our support team</p>
                  </div>
                  <Button 
                    variant="outline" 
                    size="sm"
                    onClick={() => window.location.href = 'mailto:support@vendorconnect.com'}
                  >
                    Contact
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
      </MainLayout>
    </RoleGuard>
  );
}