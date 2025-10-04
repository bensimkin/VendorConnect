'use client';

import { useState, useEffect } from 'react';
import { toast } from 'react-hot-toast';
import MainLayout from '@/components/layout/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Settings, Save, RefreshCw } from 'lucide-react';
import { apiClient } from '@/lib/api-client';

interface ProjectSettings {
  allow_multiple_clients_per_project: boolean;
  require_project_client: boolean;
  max_clients_per_project: number;
}

interface AutoArchiveSettings {
  auto_archive_enabled: boolean;
  auto_archive_days: number;
}

export default function AdminSettingsPage() {
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [settings, setSettings] = useState<ProjectSettings>({
    allow_multiple_clients_per_project: false,
    require_project_client: true,
    max_clients_per_project: 5,
  });

  const [autoArchiveSettings, setAutoArchiveSettings] = useState<AutoArchiveSettings>({
    auto_archive_enabled: false,
    auto_archive_days: 30,
  });

  useEffect(() => {
    fetchSettings();
  }, []);

  const fetchSettings = async () => {
    try {
      setLoading(true);
      const [projectResponse, autoArchiveResponse] = await Promise.all([
        apiClient.get('/settings/project'),
        apiClient.get('/settings/auto-archive')
      ]);
      setSettings(projectResponse.data.data);
      setAutoArchiveSettings(autoArchiveResponse.data.data);
    } catch (error) {
      console.error('Failed to fetch settings:', error);
      toast.error('Failed to load settings');
    } finally {
      setLoading(false);
    }
  };

  const handleSettingChange = (key: keyof ProjectSettings, value: boolean | number) => {
    setSettings(prev => ({ ...prev, [key]: value }));
  };

  const handleAutoArchiveSettingChange = (key: keyof AutoArchiveSettings, value: boolean | number) => {
    setAutoArchiveSettings(prev => ({ ...prev, [key]: value }));
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      // Save project settings
      const projectPromises = Object.entries(settings).map(([key, value]) =>
        apiClient.put(`/settings/${key}`, { value })
      );
      
      // Save auto-archive settings
      const autoArchivePromise = apiClient.put('/settings/auto-archive', autoArchiveSettings);
      
      await Promise.all([...projectPromises, autoArchivePromise]);
      toast.success('Settings saved successfully');
    } catch (error: any) {
      console.error('Failed to save settings:', error);
      toast.error(error.response?.data?.message || 'Failed to save settings');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p className="mt-2 text-muted-foreground">Loading settings...</p>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between flex-wrap gap-2">
          <div>
            <h1 className="text-2xl font-bold">Admin Settings</h1>
            <p className="text-muted-foreground">Manage application configuration</p>
          </div>
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              size="sm"
              onClick={fetchSettings}
              disabled={saving}
            >
              <RefreshCw className="h-4 w-4 mr-2" />
              Refresh
            </Button>
            <Button
              onClick={handleSave}
              disabled={saving}
            >
              <Save className="h-4 w-4 mr-2" />
              {saving ? 'Saving...' : 'Save Settings'}
            </Button>
          </div>
        </div>

        {/* Project Settings */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Settings className="h-5 w-5" />
              Project Settings
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            {/* Multiple Clients Setting */}
            <div className="flex items-center justify-between">
              <div className="space-y-1">
                <Label className="text-base font-medium">
                  Allow Multiple Clients per Project
                </Label>
                <p className="text-sm text-muted-foreground">
                  Enable this to allow multiple clients to be assigned to a single project
                </p>
              </div>
              <div className="flex items-center gap-2">
                <Switch
                  checked={settings.allow_multiple_clients_per_project}
                  onCheckedChange={(checked) => 
                    handleSettingChange('allow_multiple_clients_per_project', checked)
                  }
                />
                <Badge variant={settings.allow_multiple_clients_per_project ? "default" : "secondary"}>
                  {settings.allow_multiple_clients_per_project ? "Enabled" : "Disabled"}
                </Badge>
              </div>
            </div>

            {/* Require Project Client Setting */}
            <div className="flex items-center justify-between">
              <div className="space-y-1">
                <Label className="text-base font-medium">
                  Require Project Client
                </Label>
                <p className="text-sm text-muted-foreground">
                  Require at least one client to be assigned to a project
                </p>
              </div>
              <div className="flex items-center gap-2">
                <Switch
                  checked={settings.require_project_client}
                  onCheckedChange={(checked) => 
                    handleSettingChange('require_project_client', checked)
                  }
                />
                <Badge variant={settings.require_project_client ? "default" : "secondary"}>
                  {settings.require_project_client ? "Required" : "Optional"}
                </Badge>
              </div>
            </div>

            {/* Max Clients per Project Setting */}
            <div className="flex items-center justify-between">
              <div className="space-y-1">
                <Label className="text-base font-medium">
                  Maximum Clients per Project
                </Label>
                <p className="text-sm text-muted-foreground">
                  Maximum number of clients that can be assigned to a single project
                </p>
              </div>
              <div className="flex items-center gap-2">
                <Input
                  type="number"
                  min="1"
                  max="20"
                  value={settings.max_clients_per_project}
                  onChange={(e) => 
                    handleSettingChange('max_clients_per_project', parseInt(e.target.value) || 1)
                  }
                  className="w-20"
                  disabled={!settings.allow_multiple_clients_per_project}
                />
                <Badge variant="outline">
                  Max {settings.max_clients_per_project}
                </Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Auto-Archive Settings */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Settings className="h-5 w-5" />
              Auto-Archive Settings
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            {/* Auto-Archive Enabled Setting */}
            <div className="flex items-center justify-between">
              <div className="space-y-1">
                <Label className="text-base font-medium">
                  Enable Auto-Archive
                </Label>
                <p className="text-sm text-muted-foreground">
                  Automatically archive completed tasks after a specified number of days
                </p>
              </div>
              <div className="flex items-center gap-2">
                <Switch
                  checked={autoArchiveSettings.auto_archive_enabled}
                  onCheckedChange={(checked) => 
                    handleAutoArchiveSettingChange('auto_archive_enabled', checked)
                  }
                />
                <Badge variant={autoArchiveSettings.auto_archive_enabled ? "default" : "secondary"}>
                  {autoArchiveSettings.auto_archive_enabled ? "Enabled" : "Disabled"}
                </Badge>
              </div>
            </div>

            {/* Auto-Archive Days Setting */}
            <div className="flex items-center justify-between">
              <div className="space-y-1">
                <Label className="text-base font-medium">
                  Archive After (Days)
                </Label>
                <p className="text-sm text-muted-foreground">
                  Number of days after completion to automatically archive tasks
                </p>
              </div>
              <div className="flex items-center gap-2">
                <Input
                  type="number"
                  min="1"
                  max="365"
                  value={autoArchiveSettings.auto_archive_days}
                  onChange={(e) => 
                    handleAutoArchiveSettingChange('auto_archive_days', parseInt(e.target.value) || 30)
                  }
                  className="w-20"
                  disabled={!autoArchiveSettings.auto_archive_enabled}
                />
                <Badge variant="outline">
                  {autoArchiveSettings.auto_archive_days} days
                </Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Information Card */}
        <Card>
          <CardHeader>
            <CardTitle>How These Settings Work</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <h4 className="font-medium">Multiple Clients per Project</h4>
              <ul className="text-sm text-muted-foreground space-y-1 ml-4">
                <li>• <strong>Enabled:</strong> Projects can have multiple clients assigned</li>
                <li>• <strong>Disabled:</strong> Projects can only have one client assigned</li>
                <li>• When disabled, the project forms will only show single client selection</li>
              </ul>
            </div>
            
            <div className="space-y-2">
              <h4 className="font-medium">Require Project Client</h4>
              <ul className="text-sm text-muted-foreground space-y-1 ml-4">
                <li>• <strong>Required:</strong> All projects must have at least one client</li>
                <li>• <strong>Optional:</strong> Projects can be created without any clients</li>
              </ul>
            </div>
            
            <div className="space-y-2">
              <h4 className="font-medium">Maximum Clients per Project</h4>
              <ul className="text-sm text-muted-foreground space-y-1 ml-4">
                <li>• Limits how many clients can be assigned to a single project</li>
                <li>• Only applies when "Multiple Clients per Project" is enabled</li>
                <li>• Helps prevent projects from becoming too complex to manage</li>
              </ul>
            </div>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}
