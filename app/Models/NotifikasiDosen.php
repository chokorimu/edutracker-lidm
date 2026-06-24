<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifikasiDosen extends Model
{
    protected $table = 'notifikasi_dosen';

    protected $fillable = ['dosen_id', 'mata_kuliah_id', 'tugas_id', 'judul', 'pesan', 'tipe', 'sumber', 'is_read'];

    public function dosen()
    {
        return $this->belongsTo(UserDosen::class, 'dosen_id');
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }
}
