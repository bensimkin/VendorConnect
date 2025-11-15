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
        Schema::table('settings', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->index('admin_id');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });

        // Drop the old unique constraint on key alone
        DB::statement("ALTER TABLE settings DROP INDEX unique_key");
        
        // Add new unique constraint on key + admin_id combination
        DB::statement("ALTER TABLE settings ADD UNIQUE KEY unique_key_admin (admin_id, `key`)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Drop the combined unique constraint
            DB::statement("ALTER TABLE settings DROP INDEX unique_key_admin");
            
            $table->dropForeign(['admin_id']);
            $table->dropIndex(['admin_id']);
            $table->dropColumn('admin_id');
            
            // Restore original unique constraint
            DB::statement("ALTER TABLE settings ADD UNIQUE KEY unique_key (`key`)");
        });
    }
};

