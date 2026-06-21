<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserAdmin;

class UserAdminSeeder extends Seeder
{
    public function run(): void
    {
        UserAdmin::create([
            'name' => 'Super Admin',
            'email' => 'admin@edutrack.test',
            'password' => 'admin123', // otomatis ke-hash karena cast 'hashed'
        ]);
    }
}