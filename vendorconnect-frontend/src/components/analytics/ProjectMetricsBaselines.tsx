'use client';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { TrendingUp, Clock, Target, Users, Building2 } from 'lucide-react';

interface BaselineMetric {
  metric_name: string;
  metric_value: number;
  sample_size: number;
  client?: {
    id: number;
    name: string;
  };
  task_type?: {
    id: number;
    name: string;
  };
  calculated_at: string;
}

interface ProjectMetricsBaselinesProps {
  baselines: {
    overall_metrics: BaselineMetric[];
    by_client: BaselineMetric[];
    by_task_type: BaselineMetric[];
  };
  summary: {
    total_baselines: number;
    last_calculated: string | null;
  };
}

export default function ProjectMetricsBaselines({ baselines, summary }: ProjectMetricsBaselinesProps) {
  const formatMetricName = (name: string) => {
    const names: Record<string, string> = {
      'avg_duration_days': 'Avg Project Duration',
      'avg_task_count': 'Avg Tasks/Project',
      'avg_task_completion_velocity': 'Avg Task Velocity',
    };
    return names[name] || name;
  };

  const formatMetricValue = (name: string, value: number) => {
    if (name === 'avg_duration_days') {
      return `${value.toFixed(1)} days`;
    } else if (name === 'avg_task_count') {
      return value.toFixed(1);
    } else if (name === 'avg_task_completion_velocity') {
      return `${value.toFixed(2)} tasks/day`;
    }
    return value.toFixed(2);
  };

  const getMetricIcon = (name: string) => {
    if (name.includes('duration')) return Clock;
    if (name.includes('count')) return Target;
    if (name.includes('velocity')) return TrendingUp;
    return TrendingUp;
  };

  return (
    <div className="space-y-6">
      {/* Summary Header */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <TrendingUp className="w-5 h-5" />
            Project Metrics Baselines
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
              <Target className="w-8 h-8 text-blue-600 dark:text-blue-400" />
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400">Total Baselines</p>
                <p className="text-2xl font-bold text-blue-600 dark:text-blue-400">{summary.total_baselines}</p>
              </div>
            </div>
            <div className="flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
              <Clock className="w-8 h-8 text-green-600 dark:text-green-400" />
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400">Last Calculated</p>
                <p className="text-lg font-semibold text-green-600 dark:text-green-400">
                  {summary.last_calculated ? new Date(summary.last_calculated).toLocaleDateString() : 'N/A'}
                </p>
              </div>
            </div>
            <div className="flex items-center gap-3 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
              <Building2 className="w-8 h-8 text-purple-600 dark:text-purple-400" />
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400">Clients Tracked</p>
                <p className="text-2xl font-bold text-purple-600 dark:text-purple-400">
                  {new Set(baselines.by_client.map(b => b.client?.id)).size}
                </p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Overall Metrics */}
      {baselines.overall_metrics.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Overall Project Metrics</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {baselines.overall_metrics.map((metric, index) => {
                const Icon = getMetricIcon(metric.metric_name);
                return (
                  <div key={index} className="p-4 border rounded-lg hover:shadow-md transition-shadow">
                    <div className="flex items-center gap-3 mb-2">
                      <Icon className="w-6 h-6 text-blue-600" />
                      <h3 className="font-semibold">{formatMetricName(metric.metric_name)}</h3>
                    </div>
                    <p className="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                      {formatMetricValue(metric.metric_name, metric.metric_value)}
                    </p>
                    <p className="text-sm text-gray-600 dark:text-gray-400">
                      Based on {metric.sample_size} project{metric.sample_size !== 1 ? 's' : ''}
                    </p>
                  </div>
                );
              })}
            </div>
          </CardContent>
        </Card>
      )}

      {/* By Client */}
      {baselines.by_client.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Users className="w-5 h-5" />
              Metrics by Client
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b">
                    <th className="text-left py-3 px-4">Client</th>
                    <th className="text-left py-3 px-4">Metric</th>
                    <th className="text-right py-3 px-4">Value</th>
                    <th className="text-right py-3 px-4">Sample Size</th>
                  </tr>
                </thead>
                <tbody>
                  {baselines.by_client.map((metric, index) => (
                    <tr key={index} className="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                      <td className="py-3 px-4 font-medium">{metric.client?.name || 'Unknown'}</td>
                      <td className="py-3 px-4 text-gray-600 dark:text-gray-400">
                        {formatMetricName(metric.metric_name)}
                      </td>
                      <td className="py-3 px-4 text-right font-semibold">
                        {formatMetricValue(metric.metric_name, metric.metric_value)}
                      </td>
                      <td className="py-3 px-4 text-right text-sm text-gray-600 dark:text-gray-400">
                        {metric.sample_size}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      )}

      {/* By Task Type */}
      {baselines.by_task_type.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Target className="w-5 h-5" />
              Metrics by Task Type
            </CardTitle>
          </CardHeader>
          <CardContent  className="max-h-[500px] overflow-y-auto">
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b">
                    <th className="text-left py-3 px-4">Task Type</th>
                    <th className="text-left py-3 px-4">Metric</th>
                    <th className="text-right py-3 px-4">Value</th>
                    <th className="text-right py-3 px-4">Sample Size</th>
                  </tr>
                </thead>
                <tbody>
                  {baselines.by_task_type.map((metric, index) => (
                    <tr key={index} className="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                      <td className="py-3 px-4 font-medium">{metric.task_type?.name || 'Unknown'}</td>
                      <td className="py-3 px-4 text-gray-600 dark:text-gray-400">
                        {formatMetricName(metric.metric_name)}
                      </td>
                      <td className="py-3 px-4 text-right font-semibold">
                        {formatMetricValue(metric.metric_name, metric.metric_value)}
                      </td>
                      <td className="py-3 px-4 text-right text-sm text-gray-600 dark:text-gray-400">
                        {metric.sample_size}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Empty State */}
      {baselines.overall_metrics.length === 0 && 
       baselines.by_client.length === 0 && 
       baselines.by_task_type.length === 0 && (
        <Card>
          <CardContent className="py-12">
            <div className="text-center">
              <TrendingUp className="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                No Metrics Available
              </h3>
              <p className="text-gray-600 dark:text-gray-400 mb-4">
                Historical project metrics will appear here once projects are completed and baselines are calculated.
              </p>
              <p className="text-sm text-gray-500 dark:text-gray-500">
                Run the command: <code className="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">php artisan metrics:calculate-project-baselines</code>
              </p>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
