<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        $adminExists = User::where('email', 'admin@sitejo.com')->exists();

        if (!$adminExists) {
            User::create([
                'name' => 'Administrator',
                'email' => 'admin@sitejo.com',
                'nim_nip' => 'ADMIN001',
                'role' => 'admin',
                'phone' => '081234567890',
                'password' => Hash::make('admin123'), // Change this password after first login
            ]);

            echo "✅ Admin user created successfully!\n";
            echo "Email: admin@sitejo.com\n";
            echo "Password: admin123\n";
            echo "⚠️ Please change the password after first login!\n";
        } else {
            echo "ℹ️ Admin user already exists.\n";
        }
    }
}
