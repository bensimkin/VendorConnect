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
        Schema::table('ch_messages', function (Blueprint $table) {
            // Add task_id column if it doesn't exist
            if (!Schema::hasColumn('ch_messages', 'task_id')) {
                $table->unsignedBigInteger('task_id')->nullable()->after('sender_id');
                $table->foreign('task_id')->references('id')->on('tasks')->onDelete('set null');
            }
            
            // Add indexes for efficient querying by task and date
            $table->index(['task_id', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ch_messages', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['task_id', 'created_at']);
            $table->dropIndex(['created_at']);
            
            // Drop task_id column if it was added
            if (Schema::hasColumn('ch_messages', 'task_id')) {
                $table->dropForeign(['task_id']);
                $table->dropColumn('task_id');
            }
        });
    }
};
