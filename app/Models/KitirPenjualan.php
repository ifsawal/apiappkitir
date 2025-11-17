<?php

namespace App\Models;

use App\Models\Pangkalan;
use App\Models\KitirPenjualanBriva;
use Illuminate\Database\Eloquent\Model;

class KitirPenjualan extends Model
{
    public $timestamps = false;
    protected $table = 'kitir_penjualan';

    public function pangkalan()
    {
        return $this->belongsTo(Pangkalan::class, 'id_pang', 'id_pang');
    }

    public function kitir_penjualan_briva()
    {
        return $this->hasOne(KitirPenjualanBriva::class, 'kitir_penjualan_ID', 'id');
    }
}
