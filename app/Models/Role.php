<?php

namespace App\Models;

use App\Models\User;

class Role extends \Spatie\Permission\Models\Role
{
    public function user()
    {
        return $this->belongsToMany(User::class, 'model_has_roles', 'role_id', 'model_id');
    }
}
