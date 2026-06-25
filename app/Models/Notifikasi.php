<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';

    protected $fillable = ['siswa_id', 'judul', 'pesan', 'tipe', 'sumber', 'is_read'];

    public function siswa()
    {
        return $this->belongsTo(UserSiswa::class, 'siswa_id');
    }
}
