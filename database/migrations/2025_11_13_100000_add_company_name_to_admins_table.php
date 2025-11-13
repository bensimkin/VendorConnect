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
        Schema::table('admins', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('user_id');
            $table->string('company_email')->nullable()->after('company_name');
            $table->string('company_phone')->nullable()->after('company_email');
            $table->text('company_address')->nullable()->after('company_phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'company_email', 'company_phone', 'company_address']);
        });
    }
};

