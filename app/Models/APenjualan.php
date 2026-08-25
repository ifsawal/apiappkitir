<?php

namespace App\Models;

use App\Models\APenjualanDetil;
use App\Models\Pangkalan2;
use Illuminate\Database\Eloquent\Model;

class APenjualan extends Model
{
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
    ];
    protected $appends = ['created_at_format'];
    public function detil()
    {
        return $this->hasMany(APenjualanDetil::class, 'penjualan_id', 'id');
    }

    public function pangkalan2()
    {
        return $this->belongsTo(Pangkalan2::class, 'pangkalan2_id', 'id');
    }


    public function getCreatedAtFormatAttribute()
    {
        return $this->created_at?->format('d-m-Y H:i:s');
    }
}
