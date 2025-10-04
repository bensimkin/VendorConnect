<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add 'Submitted' status to the statuses table
        DB::table('statuses')->insert([
            [
                'title' => 'Submitted',
                'slug' => 'submitted',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the 'Submitted' status
        DB::table('statuses')->where('slug', 'submitted')->delete();
    }
};
