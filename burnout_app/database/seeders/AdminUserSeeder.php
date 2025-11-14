<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin user.
     */
    public function run(): void
    {
        // Check if admin user already exists
        $admin = User::where('email', 'admin@email.com')->first();
        
        if (!$admin) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@email.com',
                'password' => Hash::make('admin'),
            ]);
        } else {
            // Update password if user exists
            $admin->password = Hash::make('admin');
            $admin->save();
        }
    }
}

