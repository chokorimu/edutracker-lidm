<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $table = 'laporan';
    protected $fillable = ['judul', 'tipe', 'periode', 'file_path', 'created_by'];

    public function admin()
    {
        return $this->belongsTo(UserAdmin::class, 'created_by');
    }
}
