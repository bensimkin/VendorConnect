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
            $table->text('standard_brief')->nullable()->after('template_name');
            $table->text('description')->nullable()->after('standard_brief');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_brief_templates', function (Blueprint $table) {
            $table->dropColumn(['standard_brief', 'description']);
        });
    }
};
