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
        // Drop the old notification tables
        Schema::dropIfExists('client_notifications');
        Schema::dropIfExists('notification_user');
        Schema::dropIfExists('notifications');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the old tables if needed (for rollback)
        // Note: This is a simplified recreation - you may need to adjust based on your original schema
        
        // Recreate notifications table (basic structure)
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        // Recreate client_notifications table (basic structure)
        Schema::create('client_notifications', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        // Recreate notification_user table (basic structure)
        Schema::create('notification_user', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};