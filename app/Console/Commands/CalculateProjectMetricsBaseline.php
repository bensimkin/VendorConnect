<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\Task;
use App\Models\Status;
use App\Models\ProjectMetricsBaseline;
use App\Models\Client;
use App\Models\TaskType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculateProjectMetricsBaseline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:calculate-project-baselines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and store historical project metrics baselines for comparison and AI insights';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting project metrics baseline calculation...');
        
        try {
            // Get completed status
            $completedStatus = Status::getCompletedStatus();
            
            if (!$completedStatus) {
                $this->error('Completed status not found. Cannot calculate baselines.');
                return 1;
            }

            // Clear existing baselines
            $this->info('Clearing existing baselines...');
            ProjectMetricsBaseline::truncate();

            // 1. Calculate overall average project duration
            $this->calculateOverallProjectDuration($completedStatus);
            
            // 2. Calculate average project duration by client
            $this->calculateProjectDurationByClient($completedStatus);
            
            // 3. Calculate average number of tasks per project
            $this->calculateAverageTasksPerProject($completedStatus);
            
            // 4. Calculate average task completion velocity
            $this->calculateTaskCompletionVelocity($completedStatus);
            
            // 5. Calculate average project duration by task type
            $this->calculateProjectDurationByTaskType($completedStatus);

            $this->info('Project metrics baseline calculation completed successfully!');
            
            // Display summary
            $totalMetrics = ProjectMetricsBaseline::count();
            $this->info("Stored {$totalMetrics} baseline metrics.");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error calculating baselines: ' . $e->getMessage());
            Log::error('CalculateProjectMetricsBaseline failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Calculate overall average project duration across all completed projects
     */
    private function calculateOverallProjectDuration($completedStatus)
    {
        $this->info('Calculating overall project duration...');
        
        $completedProjects = Project::where('status_id', $completedStatus->id)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get();

        if ($completedProjects->count() === 0) {
            $this->warn('No completed projects with valid dates found.');
            return;
        }

        $totalDays = 0;
        $count = 0;

        foreach ($completedProjects as $project) {
            $days = $this->calculateDaysBetween($project->start_date, $project->end_date);
            if ($days !== null) {
                $totalDays += $days;
                $count++;
            }
        }

        if ($count > 0) {
            $avgDuration = $totalDays / $count;
            
            ProjectMetricsBaseline::create([
                'metric_name' => 'avg_duration_days',
                'metric_value' => round($avgDuration, 2),
                'sample_size' => $count,
                'task_type_id' => null,
                'client_id' => null,
                'calculated_at' => now(),
            ]);

            $this->info("  Overall average: {$avgDuration} days (from {$count} projects)");
        }
    }

    /**
     * Calculate average project duration by client
     */
    private function calculateProjectDurationByClient($completedStatus)
    {
        $this->info('Calculating project duration by client...');
        
        // Get all completed projects with valid dates
        $completedProjects = Project::where('status_id', $completedStatus->id)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->with('clients')
            ->get();

        // Get all unique clients who have completed projects
        $clientIds = DB::table('client_project')
            ->join('projects', 'client_project.project_id', '=', 'projects.id')
            ->where('projects.status_id', $completedStatus->id)
            ->whereNotNull('projects.start_date')
            ->whereNotNull('projects.end_date')
            ->select('client_project.client_id')
            ->distinct()
            ->pluck('client_id');

        foreach ($clientIds as $clientId) {
            $client = Client::find($clientId);
            if (!$client) {
                continue;
            }

            // Get projects for this client
            $projects = Project::where('status_id', $completedStatus->id)
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->whereHas('clients', function($query) use ($clientId) {
                    $query->where('clients.id', $clientId);
                })
                ->get();

            if ($projects->count() === 0) {
                continue;
            }

            $totalDays = 0;
            $count = 0;

            foreach ($projects as $project) {
                $days = $this->calculateDaysBetween($project->start_date, $project->end_date);
                if ($days !== null) {
                    $totalDays += $days;
                    $count++;
                }
            }

            if ($count > 0) {
                $avgDuration = $totalDays / $count;
                
                $companyName = $client->company ?? ($client->first_name . ' ' . $client->last_name);
                
                ProjectMetricsBaseline::create([
                    'metric_name' => 'avg_duration_days',
                    'metric_value' => round($avgDuration, 2),
                    'sample_size' => $count,
                    'task_type_id' => null,
                    'client_id' => $client->id,
                    'calculated_at' => now(),
                ]);

                $this->info("  Client '{$companyName}' ({$client->id}): {$avgDuration} days (from {$count} projects)");
            }
        }
    }

    /**
     * Calculate average number of tasks per project
     */
    private function calculateAverageTasksPerProject($completedStatus)
    {
        $this->info('Calculating average tasks per project...');
        
        $completedProjects = Project::where('status_id', $completedStatus->id)->get();

        if ($completedProjects->count() === 0) {
            $this->warn('No completed projects found.');
            return;
        }

        $totalTasks = 0;
        $count = 0;

        foreach ($completedProjects as $project) {
            $taskCount = Task::where('project_id', $project->id)->count();
            if ($taskCount > 0) {
                $totalTasks += $taskCount;
                $count++;
            }
        }

        if ($count > 0) {
            $avgTasks = $totalTasks / $count;
            
            ProjectMetricsBaseline::create([
                'metric_name' => 'avg_task_count',
                'metric_value' => round($avgTasks, 2),
                'sample_size' => $count,
                'task_type_id' => null,
                'client_id' => null,
                'calculated_at' => now(),
            ]);

            $this->info("  Average tasks per project: {$avgTasks} (from {$count} projects)");
        }
    }

    /**
     * Calculate average task completion velocity (tasks per day)
     */
    private function calculateTaskCompletionVelocity($completedStatus)
    {
        $this->info('Calculating task completion velocity...');
        
        $completedProjects = Project::where('status_id', $completedStatus->id)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get();

        if ($completedProjects->count() === 0) {
            $this->warn('No completed projects with valid dates found.');
            return;
        }

        $totalVelocities = [];
        
        foreach ($completedProjects as $project) {
            $days = $this->calculateDaysBetween($project->start_date, $project->end_date);
            $taskCount = Task::where('project_id', $project->id)->count();
            
            if ($days !== null && $days > 0 && $taskCount > 0) {
                $velocity = $taskCount / $days;
                $totalVelocities[] = $velocity;
            }
        }

        if (count($totalVelocities) > 0) {
            $avgVelocity = array_sum($totalVelocities) / count($totalVelocities);
            
            ProjectMetricsBaseline::create([
                'metric_name' => 'avg_task_completion_velocity',
                'metric_value' => round($avgVelocity, 2),
                'sample_size' => count($totalVelocities),
                'task_type_id' => null,
                'client_id' => null,
                'calculated_at' => now(),
            ]);

            $this->info("  Average task velocity: {$avgVelocity} tasks/day (from " . count($totalVelocities) . " projects)");
        }
    }

    /**
     * Calculate average project duration by task type
     */
    private function calculateProjectDurationByTaskType($completedStatus)
    {
        $this->info('Calculating project duration by task type...');
        
        // Get distinct task types from completed projects
        $taskTypes = TaskType::has('tasks')->get();

        foreach ($taskTypes as $taskType) {
            // Get projects that contain this task type
            $projects = Project::where('status_id', $completedStatus->id)
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->whereHas('tasks', function($query) use ($taskType) {
                    $query->where('task_type_id', $taskType->id);
                })
                ->get();

            if ($projects->count() === 0) {
                continue;
            }

            $totalDays = 0;
            $count = 0;

            foreach ($projects as $project) {
                $days = $this->calculateDaysBetween($project->start_date, $project->end_date);
                if ($days !== null) {
                    $totalDays += $days;
                    $count++;
                }
            }

            if ($count > 0) {
                $avgDuration = $totalDays / $count;
                
                ProjectMetricsBaseline::create([
                    'metric_name' => 'avg_duration_days',
                    'metric_value' => round($avgDuration, 2),
                    'sample_size' => $count,
                    'task_type_id' => $taskType->id,
                    'client_id' => null,
                    'calculated_at' => now(),
                ]);

                $this->info("  Task type '{$taskType->task_type}' ({$taskType->id}): {$avgDuration} days (from {$count} projects)");
            }
        }
    }

    /**
     * Calculate days between two dates
     * Returns null if either date is null or if dates are invalid
     */
    private function calculateDaysBetween($startDate, $endDate)
    {
        if ($startDate === null || $endDate === null) {
            return null;
        }

        try {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            
            if ($end->lt($start)) {
                return null;
            }
            
            return $start->diffInDays($end);
        } catch (\Exception $e) {
            return null;
        }
    }
}
