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
        Schema::table('settings', function (Blueprint $table) {
            // Add new columns
            $table->string('key')->nullable()->after('id');
            $table->string('type')->default('string')->after('value');
            $table->string('group')->default('general')->after('type');
            $table->text('description')->nullable()->after('group');
        });

        // Migrate existing data
        DB::statement("UPDATE settings SET `key` = `variable` WHERE `key` IS NULL");
        
        // Drop old column
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('variable');
        });

        // Make key column not nullable and unique
        Schema::table('settings', function (Blueprint $table) {
            $table->string('key')->nullable(false)->change();
        });

        // Add unique constraint
        DB::statement("ALTER TABLE settings ADD UNIQUE KEY unique_key (`key`)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('variable')->nullable()->after('id');
        });

        // Migrate data back
        DB::statement("UPDATE settings SET `variable` = `key`");

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['key', 'type', 'group', 'description']);
        });
    }
};
