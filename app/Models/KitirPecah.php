<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitirPecah extends Model
{
    public $timestamps = false;
    protected $table = 'kitir_pecah';

    public function kitir_penjualan()
    {
        return $this->belongsTo(KitirPenjualan::class, 'kitir_penjualan_id', 'id');
    }
}
