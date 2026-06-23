<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserAdmin;

class UserAdminSeeder extends Seeder
{
    public function run(): void
    {
        UserAdmin::updateOrCreate(
            ['email' => 'admin@edutrack.test'],
            [
                'name' => 'Super Admin',
                'password' => 'admin123',
            ]
        );
    }
}
