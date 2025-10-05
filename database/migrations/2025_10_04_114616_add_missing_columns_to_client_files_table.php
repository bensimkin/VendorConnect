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
        Schema::table('client_files', function (Blueprint $table) {
            $table->string('file_category')->default('client_file')->comment('Category: brand_guide or client_file')->after('file_path');
            $table->string('file_name')->nullable()->after('file_category');
            $table->string('file_type')->nullable()->after('file_name');
            $table->integer('file_size')->nullable()->after('file_type');
            $table->text('description')->nullable()->after('file_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_files', function (Blueprint $table) {
            $table->dropColumn(['file_category', 'file_name', 'file_type', 'file_size', 'description']);
        });
    }
};
