<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TugasSubmission extends Model
{
    protected $table = 'tugas_submission';

    protected $fillable = ['tugas_id', 'siswa_id', 'file_path', 'file_name', 'submitted_at', 'status'];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    public function siswa()
    {
        return $this->belongsTo(UserSiswa::class, 'siswa_id');
    }
}
