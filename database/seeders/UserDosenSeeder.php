<?php

namespace Database\Seeders;

use App\Models\UserDosen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserDosenSeeder extends Seeder
{
    public function run(): void
    {
        UserDosen::updateOrCreate(
            ['email' => 'dosen@edutrack.test'],
            [
                'name' => 'Dr. Rahmat Hidayat, S.Kom., M.T.',
                'password' => Hash::make('dosen123'),
                'nidn' => 'NIDN-001',
                'fakultas' => 'Ilmu Komputer',
            ]
        );
        
        UserDosen::updateOrCreate(
            ['email' => 'andi.pratama@kampus.ac.id'],
            [
                'name' => 'Dr. Andi Pratama, S.Kom., M.Kom.',
                'password' => Hash::make('password123'),
                'nidn' => '0012018501',
                'fakultas' => 'Fakultas Ilmu Komputer',
            ]
        );

        UserDosen::updateOrCreate(
            ['email' => 'budi.santoso@kampus.ac.id'],
            [
                'name' => 'Dr. Budi Santoso, S.T., M.Kom.',
                'password' => Hash::make('password123'),
                'nidn' => '0013028602',
                'fakultas' => 'Fakultas Ilmu Komputer',
            ]
        );

        UserDosen::updateOrCreate(
            ['email' => 'citra.lestari@kampus.ac.id'],
            [
                'name' => 'Citra Lestari, S.Kom., M.Kom.',
                'password' => Hash::make('password123'),
                'nidn' => '0014038703',
                'fakultas' => 'Fakultas Ilmu Komputer',
            ]
        );

        UserDosen::updateOrCreate(
            ['email' => 'dedi.kurniawan@kampus.ac.id'],
            [
                'name' => 'Dedi Kurniawan, S.Kom., M.T.',
                'password' => Hash::make('password123'),
                'nidn' => '0015048804',
                'fakultas' => 'Fakultas Ilmu Komputer',
            ]
        );

        UserDosen::updateOrCreate(
            ['email' => 'eka.wulandari@kampus.ac.id'],
            [
                'name' => 'Eka Wulandari, S.Kom., M.Kom.',
                'password' => Hash::make('password123'),
                'nidn' => '0016058905',
                'fakultas' => 'Fakultas Ilmu Komputer',
            ]
        );
    }
}
