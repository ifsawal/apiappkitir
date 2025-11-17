<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kitir extends Model
{
    protected $table = 'kitir';

    public function kitir_pecah()
    {
        return $this->hasMany(KitirPecah::class, 'id_k', 'id_k');
    }

    public function pangkalan()
    {
        return $this->belongsTo(Pangkalan::class, 'id_pang', 'id_pang');
    }

    public function bagi_pangkalan()
    {
        return $this->hasOne(BagiPangkalan::class, 'id_k', 'id_k');
    }
}
