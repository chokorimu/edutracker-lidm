<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    protected $table = 'tugas';

    protected $fillable = ['mata_kuliah_id', 'nama', 'bobot', 'deadline', 'deskripsi', 'status_beban', 'override', 'is_bobot_locked'];

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function nilaiTugas()
    {
        return $this->hasMany(NilaiTugas::class, 'tugas_id');
    }

    public function submissions()
    {
        return $this->hasMany(TugasSubmission::class, 'tugas_id');
    }

    public function notifikasiDosen()
    {
        return $this->hasMany(NotifikasiDosen::class, 'tugas_id');
    }
}
