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

    public function krs() { return $this->hasMany(Krs::class, 'siswa_id'); }
    public function nilaiTugas() { return $this->hasMany(NilaiTugas::class, 'siswa_id'); }
    public function notifikasi() { return $this->hasMany(Notifikasi::class, 'siswa_id'); }
    public function ipkHistory() { return $this->hasMany(IpkHistory::class, 'siswa_id'); }
    public function dosenPa() { return $this->hasMany(DosenPa::class, 'siswa_id'); }
}