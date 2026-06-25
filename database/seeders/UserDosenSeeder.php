<?php

namespace Database\Seeders;

use App\Models\UserDosen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserDosenSeeder extends Seeder
{
    public function run(): void
    {
        UserDosen::firstOrCreate(
            ['email' => 'dosen@edutrack.test'],
            [
                'name' => 'Dr. Rahmat Hidayat, S.Kom., M.T.',
                'password' => Hash::make('dosen123'),
                'nidn' => 'NIDN-001',
                'fakultas' => 'Ilmu Komputer',
            ]
        );
    }
}
