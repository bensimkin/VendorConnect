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
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'company')) {
                $table->string('company')->nullable()->change();
            }
            if (Schema::hasColumn('clients', 'phone')) {
                $table->string('phone')->nullable()->change();
            }
            if (Schema::hasColumn('clients', 'country_code')) {
                $table->string('country_code')->nullable()->change();
            }
            if (Schema::hasColumn('clients', 'password')) {
                $table->string('password')->nullable()->change();
            }
            if (Schema::hasColumn('clients', 'dob')) {
                $table->date('dob')->nullable()->change();
            }
            if (Schema::hasColumn('clients', 'doj')) {
                $table->date('doj')->nullable()->change();
            }
            if (Schema::hasColumn('clients', 'address')) {
                $table->text('address')->nullable()->change();
            }
            if (Schema::hasColumn('clients', 'city')) {
                $table->string('city')->nullable()->change();
            }
            if (Schema::hasColumn('clients', 'state')) {
                $table->string('state')->nullable()->change();
            }
            if (Schema::hasColumn('clients', 'country')) {
                $table->string('country')->nullable()->change();
            }
            if (Schema::hasColumn('clients', 'zip')) {
                $table->string('zip')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'company')) {
                $table->string('company')->change();
            }
            if (Schema::hasColumn('clients', 'phone')) {
                $table->string('phone')->change();
            }
            if (Schema::hasColumn('clients', 'country_code')) {
                $table->string('country_code')->change();
            }
            if (Schema::hasColumn('clients', 'password')) {
                $table->string('password')->change();
            }
            if (Schema::hasColumn('clients', 'dob')) {
                $table->date('dob')->change();
            }
            if (Schema::hasColumn('clients', 'doj')) {
                $table->date('doj')->change();
            }
            if (Schema::hasColumn('clients', 'address')) {
                $table->text('address')->change();
            }
            if (Schema::hasColumn('clients', 'city')) {
                $table->string('city')->change();
            }
            if (Schema::hasColumn('clients', 'state')) {
                $table->string('state')->change();
            }
            if (Schema::hasColumn('clients', 'country')) {
                $table->string('country')->change();
            }
            if (Schema::hasColumn('clients', 'zip')) {
                $table->string('zip')->change();
            }
        });
    }
};
