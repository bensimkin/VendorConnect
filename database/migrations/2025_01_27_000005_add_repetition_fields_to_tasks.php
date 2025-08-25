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
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_repeating')->default(false)->after('deliverable_quantity');
            $table->enum('repeat_frequency', ['daily', 'weekly', 'monthly', 'yearly'])->nullable()->after('is_repeating');
            $table->integer('repeat_interval')->default(1)->after('repeat_frequency'); // Every X days/weeks/months
            $table->date('repeat_until')->nullable()->after('repeat_interval'); // Stop repeating after this date
            $table->boolean('repeat_active')->default(true)->after('repeat_until'); // Can be stopped by admin
            $table->unsignedBigInteger('parent_task_id')->nullable()->after('repeat_active'); // Link to original repeating task
            $table->timestamp('last_repeated_at')->nullable()->after('parent_task_id');
            
            // Add foreign key for parent task
            $table->foreign('parent_task_id')->references('id')->on('tasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['parent_task_id']);
            $table->dropColumn([
                'is_repeating',
                'repeat_frequency',
                'repeat_interval',
                'repeat_until',
                'repeat_active',
                'parent_task_id',
                'last_repeated_at'
            ]);
        });
    }
};
