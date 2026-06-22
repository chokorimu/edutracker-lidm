<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserSiswa;

class UserSiswaSeeder extends Seeder
{
    public function run(): void
    {
        UserSiswa::firstOrCreate(
            ['email' => 'andi@edutrack.test'],
            [
                'name' => 'Andi Mahasiswa',
                'password' => 'siswa123',
                'nim' => '220101001',
                'prodi' => 'Teknik Informatika',
                'semester' => 3,
            ]
        );
    }
}