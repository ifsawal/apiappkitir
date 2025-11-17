<?php

namespace App\Http\Controllers\Api\v1\Pangkalan;

use App\Models\Ktp;
use DeepCopy\f002\A;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class KController extends Controller
{
    public function ktp()
    {
        $user = Auth::user();
        $ktp = Ktp::where('id_pang', $user->pangkalan_id)->get();


        return response()->json([
            'sukses' => true,
            'data' => $ktp,
        ], 202);
    }

    public function simpan_k(Request $r)
    {
        $user = Auth::user();

        $clean = trim($r->data, "()");
        $hasil = str_replace("hasil ", "", $clean);

        $items = explode("---", $hasil);

        $tersimpan = 0;
        foreach ($items as $item) {
            $parts = explode("--", $item);
            if (count($parts) == 2) {
                $parts[0] = trim($parts[0]);
                $parts[1] = trim($parts[1]); //hapus spasi

                $cek = Ktp::where('id_pang', $user->pangkalan_id)->where('ktp', $parts[0])->first();
                if ($cek) {
                    continue;
                }
                $ktp = new Ktp();
                $ktp->id_pang = $user->pangkalan_id;
                $ktp->ktp = $parts[0];
                $ktp->nama = $parts[1];
                $ktp->save();
                $tersimpan++;
            }
        }

        return response()->json([
            'sukses' => true,
            'tersimpan' => $tersimpan,
        ], 201);
    }
}
