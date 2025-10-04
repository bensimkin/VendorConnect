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
            $table->string('deliverable_title')->nullable()->after('note');
            $table->text('deliverable_description')->nullable()->after('deliverable_title');
            $table->enum('deliverable_type', ['design', 'document', 'presentation', 'other'])->nullable()->after('deliverable_description');
            $table->boolean('has_deliverable')->default(false)->after('deliverable_type');
            $table->timestamp('deliverable_completed_at')->nullable()->after('has_deliverable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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
};
