<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\UserSiswa;

class UserSiswaSeeder extends Seeder
{
    public function run(): void
    {
        UserSiswa::firstOrCreate(
            ['email' => 'andi@edutrack.test'],
            [
                'name' => 'Andi Mahasiswa',
                'password' => Hash::make('siswa123'),
                'nim' => '220101001',
                'prodi' => 'Teknik Informatika',
                'semester' => 3,
            ]
        );
    }
}
