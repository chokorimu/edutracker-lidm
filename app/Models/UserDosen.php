<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UserDosen extends Authenticatable
{
    use Notifiable;

    protected $table = 'user_dosens';

    protected $fillable = ['name', 'email', 'password', 'nidn', 'fakultas'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
}