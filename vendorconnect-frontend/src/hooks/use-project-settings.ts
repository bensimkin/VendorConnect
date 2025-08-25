import { useState, useEffect } from 'react';
import { apiClient } from '@/lib/api-client';

interface ProjectSettings {
  allow_multiple_clients_per_project: boolean;
  require_project_client: boolean;
  max_clients_per_project: number;
}

export function useProjectSettings() {
  const [settings, setSettings] = useState<ProjectSettings>({
    allow_multiple_clients_per_project: false,
    require_project_client: true,
    max_clients_per_project: 5,
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetchSettings();
  }, []);

  const fetchSettings = async () => {
    try {
      setLoading(true);
      setError(null);
      const response = await apiClient.get('/settings/project');
      setSettings(response.data.data);
    } catch (err: any) {
      console.error('Failed to fetch project settings:', err);
      setError(err.response?.data?.message || 'Failed to load project settings');
    } finally {
      setLoading(false);
    }
  };

  return {
    settings,
    loading,
    error,
    refetch: fetchSettings,
  };
}
