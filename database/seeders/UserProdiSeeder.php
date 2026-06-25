<?php

namespace Database\Seeders;

use App\Models\UserProdi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserProdiSeeder extends Seeder
{
    public function run(): void
    {
        UserProdi::create([
            'name' => 'Kaprodi Teknik Informatika',
            'email' => 'prodi@edutrack.test',
            'password' => Hash::make('prodi123'),
        ]);
    }
}
