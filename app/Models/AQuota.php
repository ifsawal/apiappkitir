<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AQuota extends Model
{
    //
    protected $casts = [
        'tanggal' => 'datetime:d-m-Y',
    ];
    public function do()
    {
        return $this->hasMany(ADo::class, 'quota_id', 'id');
    }
}
