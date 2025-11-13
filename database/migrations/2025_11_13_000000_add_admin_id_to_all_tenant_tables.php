<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add admin_id to templates table
        Schema::table('templates', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index('admin_id');
        });

        // Add admin_id to task_types table
        Schema::table('task_types', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index('admin_id');
        });

        // Add admin_id to task_brief_templates table
        Schema::table('task_brief_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index('admin_id');
        });

        // Add admin_id to task_brief_questions table
        Schema::table('task_brief_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index('admin_id');
        });

        // Add admin_id to expenses table
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index('admin_id');
        });

        // Add admin_id to expense_types table
        Schema::table('expense_types', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index('admin_id');
        });

        // Add admin_id to portfolios table
        Schema::table('portfolios', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index('admin_id');
        });

        // Add admin_id to api_keys table
        Schema::table('api_keys', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index('admin_id');
        });

        // Add admin_id to items table
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index('admin_id');
        });

        // Add admin_id to milestones table
        Schema::table('milestones', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index('admin_id');
        });

        // Add admin_id to task_brief_checklists table (if exists)
        if (Schema::hasTable('task_brief_checklists')) {
            Schema::table('task_brief_checklists', function (Blueprint $table) {
                $table->unsignedBigInteger('admin_id')->nullable()->after('id');
                $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
                $table->index('admin_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove admin_id from all tables
        $tables = [
            'templates',
            'task_types',
            'task_brief_templates',
            'task_brief_questions',
            'expenses',
            'expense_types',
            'portfolios',
            'api_keys',
            'items',
            'milestones',
        ];

        if (Schema::hasTable('task_brief_checklists')) {
            $tables[] = 'task_brief_checklists';
        }

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['{$table}_admin_id_foreign']);
                $table->dropIndex(['{$table}_admin_id_index']);
                $table->dropColumn('admin_id');
            });
        }
    }
};

