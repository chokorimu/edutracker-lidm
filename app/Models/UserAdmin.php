<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UserAdmin extends Authenticatable
{
    use Notifiable;

    protected $table = 'user_admin';

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function kalenderAkademik()
    {
        return $this->hasMany(KalenderAkademik::class, 'created_by');
    }

    public function pengaturan()
    {
        return $this->hasMany(Pengaturan::class, 'updated_by');
    }

    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'created_by');
    }
}
