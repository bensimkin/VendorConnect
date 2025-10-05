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
        Schema::create('client_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('file_path');
            $table->string('file_category')->default('client_file')->comment('Category: brand_guide or client_file');
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->index(['client_id', 'file_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_files');
    }
};
