<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ABriva extends Model
{
    protected $casts = [
        'data' => 'array',
        'trxDateTime' => 'datetime',
    ];
}
