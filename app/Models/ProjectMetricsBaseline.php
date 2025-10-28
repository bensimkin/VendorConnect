<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMetricsBaseline extends Model
{
    use HasFactory;

    protected $table = 'project_metrics_baseline';

    protected $fillable = [
        'metric_name',
        'metric_value',
        'sample_size',
        'task_type_id',
        'client_id',
        'calculated_at',
    ];

    protected $casts = [
        'metric_value' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    /**
     * Get the task type for this baseline (if applicable)
     */
    public function taskType()
    {
        return $this->belongsTo(TaskType::class);
    }

    /**
     * Get the client for this baseline (if applicable)
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
