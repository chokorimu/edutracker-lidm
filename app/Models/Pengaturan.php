<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $fillable = ['setting_key', 'value', 'updated_by'];

    public function admin()
    {
        return $this->belongsTo(UserAdmin::class, 'updated_by');
    }
}
