<?php

namespace App\Models;

use App\Models\AArmada;
use Illuminate\Database\Eloquent\Model;

class APenjualanDetil extends Model
{
    //
    public function armada()
    {
        return $this->belongsTo(AArmada::class, 'armada_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'input_user_id', 'id');
    }
}
