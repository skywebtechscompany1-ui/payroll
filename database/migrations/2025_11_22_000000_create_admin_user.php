<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAdminUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if the admin user already exists to prevent duplicate key errors
        // although migration only runs once, defensive programming is good.
        $exists = DB::table('users')->where('email', 'admin@admin.com')->exists();

        if (!$exists) {
            DB::table('users')->insert([
                'created_by' => 1,
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('123456'),
                'present_address' => 'Admin Address',
                'contact_no_one' => '0000000000',
                'gender' => 'm',
                'access_label' => 1, // 1 for superadmin
                'activation_status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('users')->where('email', 'admin@admin.com')->delete();
    }
}
