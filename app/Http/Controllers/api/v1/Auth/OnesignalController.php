<?php

namespace App\Http\Controllers\api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Models\Onesignal;
use Illuminate\Http\Request;

class OnesignalController extends Controller
{
    public function simpan_player(Request $r)
    {
        $validated = $r->validate([
            'player_id'       => ['required'],
        ]);


        $sim = new Onesignal();
        $sim->player_id = $r->player_id;
        $sim->is_login=0;
        $sim->save();

        return response()
            ->json([
                'sukses' => true,
                'pesan' => "Terdaftar",
            ], 201);
    }
}
