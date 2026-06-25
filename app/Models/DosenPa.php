<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DosenPa extends Model
{
    protected $table = 'dosen_pa';

    protected $fillable = ['dosen_id', 'siswa_id', 'tahun_ajaran'];

    public function dosen()
    {
        return $this->belongsTo(UserDosen::class, 'dosen_id');
    }

    public function siswa()
    {
        return $this->belongsTo(UserSiswa::class, 'siswa_id');
    }
}
