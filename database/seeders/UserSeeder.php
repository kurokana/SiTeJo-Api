<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Admin System',
            'email' => 'admin@example.com',
            'nim_nip' => 'ADM001',
            'role' => 'admin',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create Dosen
        $dosens = [
            [
                'name' => 'Dr. Ahmad Sudirman',
                'email' => 'ahmad.sudirman@example.com',
                'nim_nip' => '197801012005011001',
                'phone' => '081234567891',
            ],
            [
                'name' => 'Prof. Siti Nurhaliza',
                'email' => 'siti.nurhaliza@example.com',
                'nim_nip' => '198205152008012001',
                'phone' => '081234567892',
            ],
            [
                'name' => 'Dr. Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'nim_nip' => '198507202010011002',
                'phone' => '081234567893',
            ],
        ];

        foreach ($dosens as $dosen) {
            User::create([
                'name' => $dosen['name'],
                'email' => $dosen['email'],
                'nim_nip' => $dosen['nim_nip'],
                'role' => 'dosen',
                'phone' => $dosen['phone'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Create Mahasiswa
        $mahasiswas = [
            [
                'name' => 'Andi Pratama',
                'email' => 'andi.pratama@student.example.com',
                'nim_nip' => '2021110001',
                'phone' => '081234567894',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@student.example.com',
                'nim_nip' => '2021110002',
                'phone' => '081234567895',
            ],
            [
                'name' => 'Eka Putra',
                'email' => 'eka.putra@student.example.com',
                'nim_nip' => '2021110003',
                'phone' => '081234567896',
            ],
            [
                'name' => 'Fitri Handayani',
                'email' => 'fitri.handayani@student.example.com',
                'nim_nip' => '2021110004',
                'phone' => '081234567897',
            ],
            [
                'name' => 'Gilang Ramadhan',
                'email' => 'gilang.ramadhan@student.example.com',
                'nim_nip' => '2021110005',
                'phone' => '081234567898',
            ],
        ];

        foreach ($mahasiswas as $mahasiswa) {
            User::create([
                'name' => $mahasiswa['name'],
                'email' => $mahasiswa['email'],
                'nim_nip' => $mahasiswa['nim_nip'],
                'role' => 'mahasiswa',
                'phone' => $mahasiswa['phone'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }
    }
}
