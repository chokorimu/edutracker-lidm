<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UserSiswa extends Authenticatable
{
    use Notifiable;

    protected $table = 'user_siswa';

    protected $fillable = ['name', 'email', 'password', 'nim', 'prodi', 'semester'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
}