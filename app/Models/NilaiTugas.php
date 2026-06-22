<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiTugas extends Model
{
    protected $table = 'nilai_tugas';
    protected $fillable = ['tugas_id', 'siswa_id', 'nilai', 'komentar'];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    public function siswa()
    {
        return $this->belongsTo(UserSiswa::class, 'siswa_id');
    }
}
