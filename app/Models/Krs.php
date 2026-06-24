<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Krs extends Model
{
    protected $table = 'krs';

    protected $fillable = ['siswa_id', 'mata_kuliah_id', 'semester', 'tahun_ajaran', 'nilai_akhir', 'nilai_huruf', 'status'];

    public function siswa()
    {
        return $this->belongsTo(UserSiswa::class, 'siswa_id');
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }
}
