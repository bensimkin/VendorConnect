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
        Schema::table('task_brief_templates', function (Blueprint $table) {
            // Rename template_name to title
            $table->renameColumn('template_name', 'title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_brief_templates', function (Blueprint $table) {
            // Reverse the renames
            $table->renameColumn('title', 'template_name');
        });
    }
};
