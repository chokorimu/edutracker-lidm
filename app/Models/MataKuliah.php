<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    protected $table = 'mata_kuliah';

    protected $fillable = ['nama', 'kode', 'sks', 'dosen_id', 'tahun_ajaran', 'semester'];

    public function dosen()
    {
        return $this->belongsTo(UserDosen::class, 'dosen_id');
    }

    public function krs()
    {
        return $this->hasMany(Krs::class, 'mata_kuliah_id');
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'mata_kuliah_id');
    }

    public function notifikasiDosen()
    {
        return $this->hasMany(NotifikasiDosen::class, 'mata_kuliah_id');
    }
}
