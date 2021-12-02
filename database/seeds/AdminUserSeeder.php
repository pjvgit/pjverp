<?php

use App\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert default admin user
        Admin::create([
            'first_name' => 'super',
            'last_name' => 'admin',
            'email' => 'admin@legalcase.com',
            'password' => Hash::make('Admin@123'),
            'status' => 'active',
        ]);
    }
}
