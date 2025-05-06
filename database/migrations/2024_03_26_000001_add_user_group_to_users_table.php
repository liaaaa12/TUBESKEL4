<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_group', ['admin', 'customer'])->default('customer')->after('password');
        });

        // Update existing users to be admin
        DB::table('users')->where('email', 'adminMP@gmail.com')->update([
            'user_group' => 'admin'
        ]);

        // Add sample customer if not exists
        if (!DB::table('users')->where('email', 'customer@gmail.com')->exists()) {
            DB::table('users')->insert([
                'name' => 'Customer',
                'email' => 'customer@gmail.com',
                'password' => Hash::make('customer123'),
                'user_group' => 'customer',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_group');
        });
    }
}; 