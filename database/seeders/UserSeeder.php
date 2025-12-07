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
        // Create Admin (Username: Admin System, Password: 123123123)
        User::create([
            'name' => 'Admin System',
            'email' => 'admin@example.com',
            'nim_nip' => 'admin',
            'role' => 'admin',
            'phone' => '081234567890',
            'password' => Hash::make('123123123'),
            'email_verified_at' => now(),
        ]);

        // Create Dosen (Username: nama mereka, Password: 123123123)
        $dosens = [
            [
                'name' => 'Dr. Ahmad Sudirman',
                'email' => 'ahmad.sudirman@unila.ac.id',
                'nim_nip' => '197801012005011001',
                'phone' => '081234567891',
            ],
            [
                'name' => 'Prof. Siti Nurhaliza',
                'email' => 'siti.nurhaliza@unila.ac.id',
                'nim_nip' => '198205152008012001',
                'phone' => '081234567892',
            ],
            [
                'name' => 'Dr. Budi Santoso',
                'email' => 'budi.santoso@unila.ac.id',
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
                'password' => Hash::make('123123123'),
                'email_verified_at' => now(),
            ]);
        }

        // Create Mahasiswa (Username & Password: NPM mereka)
        $mahasiswas = [
            [
                'name' => 'Muhammad Faisal',
                'email' => '2315061111@students.unila.ac.id',
                'nim_nip' => '2315061111',
                'phone' => '085658630968',
            ],
            [
                'name' => 'Surya Bagaskara',
                'email' => '2315061031@students.unila.ac.id',
                'nim_nip' => '2315061031',
                'phone' => '085283338095',
            ],
        ];

        foreach ($mahasiswas as $mahasiswa) {
            // Password default = NPM
            User::create([
                'name' => $mahasiswa['name'],
                'email' => $mahasiswa['email'],
                'nim_nip' => $mahasiswa['nim_nip'],
                'role' => 'mahasiswa',
                'phone' => $mahasiswa['phone'],
                'password' => Hash::make($mahasiswa['nim_nip']),
                'email_verified_at' => now(),
            ]);
        }
    }
}
