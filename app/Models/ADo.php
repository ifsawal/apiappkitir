<?php

namespace App\Models;

use App\Models\APenjualanDetil;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ADo extends Model
{

    protected $casts = [
        'tanggal_sampai' => 'datetime:d-m-Y',
    ];
    public function supir()
    {
        return $this->belongsTo(User::class, 'supir_user_id', 'id');
    }
    public function armada()
    {
        return $this->belongsTo(AArmada::class, 'armada_id', 'id');
    }

    public function penjualan_detil()
    {
        return $this->hasMany(APenjualanDetil::class, 'do_id', 'id');
    }
}
