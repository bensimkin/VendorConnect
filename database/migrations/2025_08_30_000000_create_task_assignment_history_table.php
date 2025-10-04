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
        Schema::create('task_assignment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->enum('action', ['assigned', 'removed'])->default('assigned');
            $table->timestamp('action_date')->useCurrent();
            $table->foreignId('action_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'client_id']);
            $table->index(['task_id', 'user_id']);
            $table->index(['action_date']);
            
            // Prevent duplicate records for same task-user-client combination
            $table->unique(['task_id', 'user_id', 'client_id', 'action'], 'unique_task_user_client_action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_assignment_history');
    }
};
