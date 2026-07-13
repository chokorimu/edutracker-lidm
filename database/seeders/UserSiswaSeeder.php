<?php

namespace Database\Seeders;

use App\Models\UserSiswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSiswaSeeder extends Seeder
{
    public function run(): void
    {
        UserSiswa::updateOrCreate(
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
