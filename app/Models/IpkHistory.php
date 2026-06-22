<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpkHistory extends Model
{
    protected $table = 'ipk_history';
    protected $fillable = ['siswa_id', 'ipk', 'semester', 'tahun_ajaran', 'total_sks', 'rekomendasi_sks'];

    public function siswa()
    {
        return $this->belongsTo(UserSiswa::class, 'siswa_id');
    }
}
