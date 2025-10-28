<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_metrics_baseline', function (Blueprint $table) {
            $table->id();
            $table->string('metric_name', 100)->comment('e.g., avg_duration_days, avg_task_count, avg_task_completion_velocity');
            $table->decimal('metric_value', 10, 2)->comment('The calculated average value');
            $table->integer('sample_size')->default(0)->comment('Number of projects/tasks in this calculation');
            $table->unsignedBigInteger('task_type_id')->nullable()->comment('Optional: filter by task type');
            $table->unsignedBigInteger('client_id')->nullable()->comment('Optional: filter by client');
            $table->timestamp('calculated_at')->useCurrent();
            $table->timestamps();
            
            // Indexes for efficient querying
            $table->index('metric_name');
            $table->index('client_id');
            $table->index('task_type_id');
            $table->index('calculated_at');
            
            // Optional: Foreign key constraints
            $table->foreign('task_type_id')->references('id')->on('task_types')->onDelete('set null');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_metrics_baseline');
    }
};
