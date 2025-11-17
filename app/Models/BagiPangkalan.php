<?php

namespace App\Models;


use App\Models\UserManual;
use Illuminate\Database\Eloquent\Model;

class BagiPangkalan extends Model
{
    protected $table = 'bagi_pangkalan';

    public function user()
    {
        return $this->belongsTo(UserManual::class, 'id_u', 'id_u');
    }
}
