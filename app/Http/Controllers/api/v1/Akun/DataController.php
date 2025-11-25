<?php

namespace App\Http\Controllers\api\v1\Akun;

// use App\Models\Role;
use App\Models\Role;


// use App\Models\Permission;
use App\Models\User;
use App\Models\Permission;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Sanctum\PersonalAccessToken;



class DataController extends Controller
{
    public function akun(Request $request)
    {
        $user = User::with('roles:id,name,guard_name', 'permissions:id,name,guard_name')
            ->where('email_verified_at', '!=', null)
            ->get();
        return response()->json([
            'sukses' => true,
            'data' => $user,
        ],202);
    }

    public function role(Request $request)
    {
        $roles = Role::with([
            'permissions:id,name,guard_name',
            'user:id,name,email',
        ])->get(['id', 'name', 'guard_name']);
        return response()->json([
            'sukses' => true,
            'data' => $roles,
        ],202);
    }

    public function permisi(Request $request)
    {
        $permissions = Permission::with([
            'roles:id,name,guard_name',
            'user:id,name,email',
        ])->orderBy('id', 'desc') ->get(['id', 'name', 'guard_name']);
        return response()->json([
            'sukses' => true,
            'data' => $permissions,
        ],202);
    }

    public function data_user_aktif(Request $request)
    {
        // Ambil semua token aktif beserta user-nya
    $tokens = PersonalAccessToken::with('tokenable')
        ->where('tokenable_type', '=', 'App\\Models\\User') // hanya untuk model User
        ->whereNotNull('token')
        ->get(['id', 'tokenable_id', 'tokenable_type', 'name', 'last_used_at', 'created_at']);

        // Ambil hanya data user unik
        $users = $tokens->pluck('tokenable')->unique('id')->values();

        return response()->json([
            'sukses' => true,
            'data' => $users,
        ],202);
    }
}
