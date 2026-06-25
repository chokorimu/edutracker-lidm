<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KalenderAkademik extends Model
{
    protected $table = 'kalender_akademik';

    protected $fillable = ['judul', 'tanggal', 'tipe', 'tahun_ajaran', 'created_by'];

    public function admin()
    {
        return $this->belongsTo(UserAdmin::class, 'created_by');
    }
}
