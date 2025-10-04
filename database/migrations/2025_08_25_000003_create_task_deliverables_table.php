<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['design', 'document', 'presentation', 'file', 'link', 'other'])->default('other');
            $table->string('file_path')->nullable(); // For uploaded files
            $table->string('google_link')->nullable(); // For Google Drive/Docs links
            $table->string('external_link')->nullable(); // For other external links
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Remove deliverable fields from tasks table since we now have a separate table
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'deliverable_title',
                'deliverable_description', 
                'deliverable_type',
                'has_deliverable',
                'deliverable_completed_at'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_deliverables');
        
        // Restore deliverable fields to tasks table
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('deliverable_title')->nullable()->after('note');
            $table->text('deliverable_description')->nullable()->after('deliverable_title');
            $table->enum('deliverable_type', ['design', 'document', 'presentation', 'other'])->nullable()->after('deliverable_description');
            $table->boolean('has_deliverable')->default(false)->after('deliverable_type');
            $table->timestamp('deliverable_completed_at')->nullable()->after('has_deliverable');
        });
    }
};
