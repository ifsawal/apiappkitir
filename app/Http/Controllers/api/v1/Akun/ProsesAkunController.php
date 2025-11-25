<?php

namespace App\Http\Controllers\api\v1\Akun;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class ProsesAkunController extends Controller
{
    public function keluarkan_akun(Request $r)
    {
        // Cari token berdasarkan user ID
        $user = Auth::user();
        if ($user->id == $r->id) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Tidak dapat mengeluarkan akun sendiri.',
            ], 400);
        }

        $hap=PersonalAccessToken::where('tokenable_type', 'App\\Models\\User')
            ->where('tokenable_id', $r->id)
            ->delete();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Akun telah dikeluarkan dari sistem.',
        ], 201);
    }
}
