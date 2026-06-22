<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    protected $table = 'tugas';
    protected $fillable = ['mata_kuliah_id', 'nama', 'bobot', 'deadline', 'deskripsi', 'status_beban', 'override'];

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function nilaiTugas()
    {
        return $this->hasMany(NilaiTugas::class, 'tugas_id');
    }

    public function notifikasiDosen()
    {
        return $this->hasMany(NotifikasiDosen::class, 'tugas_id');
    }
}
