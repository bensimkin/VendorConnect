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
import { Settings, Save, RefreshCw, Globe, Building2 } from 'lucide-react';
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

interface GeneralSettings {
  timezone: string;
  company_title: string;
  date_format: string;
}

interface CompanySettings {
  company_name: string;
  company_email: string;
  company_phone: string;
  company_address: string;
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

  const [generalSettings, setGeneralSettings] = useState<GeneralSettings>({
    timezone: 'UTC',
    company_title: 'VendorConnect',
    date_format: 'DD-MM-YYYY|d-m-Y',
  });

  const [companySettings, setCompanySettings] = useState<CompanySettings>({
    company_name: '',
    company_email: '',
    company_phone: '',
    company_address: '',
  });

  useEffect(() => {
    fetchSettings();
  }, []);

  const fetchSettings = async () => {
    try {
      setLoading(true);
      const [projectResponse, autoArchiveResponse, generalResponse, companyResponse] = await Promise.all([
        apiClient.get('/settings/project'),
        apiClient.get('/settings/auto-archive'),
        apiClient.get('/settings/general'),
        apiClient.get('/settings/company')
      ]);
      setSettings(projectResponse.data.data);
      setAutoArchiveSettings(autoArchiveResponse.data.data);
      setGeneralSettings(generalResponse.data.data);
      setCompanySettings(companyResponse.data.data);
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

  const handleGeneralSettingChange = (key: keyof GeneralSettings, value: string) => {
    setGeneralSettings(prev => ({ ...prev, [key]: value }));
  };

  const handleCompanySettingChange = (key: keyof CompanySettings, value: string) => {
    setCompanySettings(prev => ({ ...prev, [key]: value }));
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
      
      // Save general settings
      const generalPromise = apiClient.put('/settings/general', generalSettings);
      
      // Save company settings
      const companyPromise = apiClient.put('/settings/company', companySettings);
      
      await Promise.all([...projectPromises, autoArchivePromise, generalPromise, companyPromise]);
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

        {/* General Settings */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Globe className="h-5 w-5" />
              General Settings
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            {/* Timezone Setting */}
            <div className="flex items-center justify-between">
              <div className="space-y-1">
                <Label className="text-base font-medium">
                  Company Timezone
                </Label>
                <p className="text-sm text-muted-foreground">
                  Set the default timezone for your company
                </p>
              </div>
              <div className="flex items-center gap-2">
                <select
                  value={generalSettings.timezone}
                  onChange={(e) => handleGeneralSettingChange('timezone', e.target.value)}
                  className="px-3 py-2 border rounded-md bg-background text-foreground min-w-[200px]"
                >
                  <optgroup label="Americas">
                    <option value="America/New_York">Eastern Time (ET)</option>
                    <option value="America/Chicago">Central Time (CT)</option>
                    <option value="America/Denver">Mountain Time (MT)</option>
                    <option value="America/Phoenix">Arizona Time (MST)</option>
                    <option value="America/Los_Angeles">Pacific Time (PT)</option>
                    <option value="America/Anchorage">Alaska Time (AKT)</option>
                    <option value="Pacific/Honolulu">Hawaii Time (HST)</option>
                    <option value="America/Toronto">Toronto</option>
                    <option value="America/Vancouver">Vancouver</option>
                    <option value="America/Mexico_City">Mexico City</option>
                    <option value="America/Sao_Paulo">São Paulo</option>
                    <option value="America/Buenos_Aires">Buenos Aires</option>
                  </optgroup>
                  <optgroup label="Europe">
                    <option value="Europe/London">London (GMT/BST)</option>
                    <option value="Europe/Paris">Paris (CET/CEST)</option>
                    <option value="Europe/Berlin">Berlin (CET/CEST)</option>
                    <option value="Europe/Rome">Rome (CET/CEST)</option>
                    <option value="Europe/Madrid">Madrid (CET/CEST)</option>
                    <option value="Europe/Amsterdam">Amsterdam (CET/CEST)</option>
                    <option value="Europe/Brussels">Brussels (CET/CEST)</option>
                    <option value="Europe/Vienna">Vienna (CET/CEST)</option>
                    <option value="Europe/Zurich">Zurich (CET/CEST)</option>
                    <option value="Europe/Stockholm">Stockholm (CET/CEST)</option>
                    <option value="Europe/Oslo">Oslo (CET/CEST)</option>
                    <option value="Europe/Helsinki">Helsinki (EET/EEST)</option>
                    <option value="Europe/Athens">Athens (EET/EEST)</option>
                    <option value="Europe/Moscow">Moscow (MSK)</option>
                    <option value="Europe/Istanbul">Istanbul (TRT)</option>
                  </optgroup>
                  <optgroup label="Asia">
                    <option value="Asia/Dubai">Dubai (GST)</option>
                    <option value="Asia/Kolkata">India (IST)</option>
                    <option value="Asia/Karachi">Karachi (PKT)</option>
                    <option value="Asia/Dhaka">Dhaka (BST)</option>
                    <option value="Asia/Bangkok">Bangkok (ICT)</option>
                    <option value="Asia/Singapore">Singapore (SGT)</option>
                    <option value="Asia/Hong_Kong">Hong Kong (HKT)</option>
                    <option value="Asia/Shanghai">China (CST)</option>
                    <option value="Asia/Tokyo">Tokyo (JST)</option>
                    <option value="Asia/Seoul">Seoul (KST)</option>
                    <option value="Asia/Manila">Manila (PHT)</option>
                    <option value="Asia/Jakarta">Jakarta (WIB)</option>
                  </optgroup>
                  <optgroup label="Australia & Pacific">
                    <option value="Australia/Sydney">Sydney (AEDT/AEST)</option>
                    <option value="Australia/Melbourne">Melbourne (AEDT/AEST)</option>
                    <option value="Australia/Brisbane">Brisbane (AEST)</option>
                    <option value="Australia/Perth">Perth (AWST)</option>
                    <option value="Pacific/Auckland">Auckland (NZDT/NZST)</option>
                    <option value="Pacific/Fiji">Fiji (FJT)</option>
                  </optgroup>
                  <optgroup label="Africa">
                    <option value="Africa/Cairo">Cairo (EET)</option>
                    <option value="Africa/Johannesburg">Johannesburg (SAST)</option>
                    <option value="Africa/Lagos">Lagos (WAT)</option>
                    <option value="Africa/Nairobi">Nairobi (EAT)</option>
                  </optgroup>
                  <optgroup label="Other">
                    <option value="UTC">UTC (Coordinated Universal Time)</option>
                  </optgroup>
                </select>
                <Badge variant="outline">
                  {generalSettings.timezone}
                </Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Company Settings */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Building2 className="h-5 w-5" />
              Company Settings
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            {/* Company Name */}
            <div className="space-y-2">
              <Label htmlFor="company_name" className="text-base font-medium">
                Company Name
              </Label>
              <Input
                id="company_name"
                type="text"
                value={companySettings.company_name || ''}
                onChange={(e) => handleCompanySettingChange('company_name', e.target.value)}
                placeholder="Enter company name"
                className="max-w-md"
              />
              <p className="text-sm text-muted-foreground">
                The name of your organization
              </p>
            </div>

            {/* Company Email */}
            <div className="space-y-2">
              <Label htmlFor="company_email" className="text-base font-medium">
                Company Email
              </Label>
              <Input
                id="company_email"
                type="email"
                value={companySettings.company_email || ''}
                onChange={(e) => handleCompanySettingChange('company_email', e.target.value)}
                placeholder="contact@company.com"
                className="max-w-md"
              />
              <p className="text-sm text-muted-foreground">
                Primary contact email for your company
              </p>
            </div>

            {/* Company Phone */}
            <div className="space-y-2">
              <Label htmlFor="company_phone" className="text-base font-medium">
                Company Phone
              </Label>
              <Input
                id="company_phone"
                type="tel"
                value={companySettings.company_phone || ''}
                onChange={(e) => handleCompanySettingChange('company_phone', e.target.value)}
                placeholder="+1 (555) 123-4567"
                className="max-w-md"
              />
              <p className="text-sm text-muted-foreground">
                Primary contact phone number
              </p>
            </div>

            {/* Company Address */}
            <div className="space-y-2">
              <Label htmlFor="company_address" className="text-base font-medium">
                Company Address
              </Label>
              <textarea
                id="company_address"
                value={companySettings.company_address || ''}
                onChange={(e) => handleCompanySettingChange('company_address', e.target.value)}
                placeholder="Enter company address"
                className="w-full max-w-md px-3 py-2 border rounded-md bg-background text-foreground min-h-[100px]"
              />
              <p className="text-sm text-muted-foreground">
                Physical address of your company
              </p>
            </div>
          </CardContent>
        </Card>

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
