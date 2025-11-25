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
        $token = $user->currentAccessToken();
        $tokenableType = $token->tokenable_type;

        if ($user->id == $r->id && $tokenableType == $r->type) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Tidak dapat mengeluarkan akun sendiri.',
            ], 400);
        }

        $hap = PersonalAccessToken::where('tokenable_type', $r->type)
            ->where('tokenable_id', $r->id)
            ->delete();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Akun telah dikeluarkan dari sistem.',
        ], 201);
    }
}
