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
        Schema::create('task_brief_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_brief_templates_id');
            $table->string('question_text');
            $table->string('question_type');
            $table->foreign('task_brief_templates_id')->references('id')->on('task_brief_templates');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_brief_questions');
    }
};
