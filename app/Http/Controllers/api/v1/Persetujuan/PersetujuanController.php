<?php

namespace App\Http\Controllers\api\v1\Persetujuan;

use App\Http\Controllers\Controller;
use App\Models\APerubahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PersetujuanController extends Controller
{
    public function setujui(Request $r)
    {
        $r->validate([
            'id' => 'required|integer|exists:a_perubahans,id',
        ]);

        $perubahan = APerubahan::where('status', 'belum')->where('id', $r->id)->first();

        if (!$perubahan) {
            return response()->json(['status' => false, 'pesan' => 'Permintaan tidak ditemukan.'], 404);
        }


        DB::transaction(function () use ($perubahan) {
            if ($perubahan->aksi == 'hapus') {
                $perubahan->nama_tabel::where('id', $perubahan->record_id)->delete();

                $perubahan->status = 'selesai';
                $perubahan->aproval_user_id = auth()->user()->id;
                $perubahan->save();
            }
        });
        return response()->json(['status' => true, 'pesan' => 'Permintaan disetujui.'], 201);
    }
}
