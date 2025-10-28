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
        Schema::table('task_user', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('user_id');
            $table->index('last_activity_at', 'idx_last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_user', function (Blueprint $table) {
            $table->dropIndex('idx_last_activity');
            $table->dropColumn('last_activity_at');
        });
    }
};
