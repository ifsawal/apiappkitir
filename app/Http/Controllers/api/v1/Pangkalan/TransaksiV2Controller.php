<?php

namespace App\Http\Controllers\api\v1\Pangkalan;

use App\Http\Controllers\Controller;
use App\Models\ADo;
use App\Models\APenjualan;
use App\Models\APenjualanDetil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransaksiV2Controller extends Controller
{
    public function ambil_penjualan_pangkalan($bulan, $tahun)
    {
        Validator::make([
            'bulan' => $bulan,
            'tahun' => $tahun,
        ], [
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|digits:4',
        ])->validate();

        $pangkalan_id = auth()->user()->id;
        $penjualan = APenjualan::select('id', 'jumlah_tabung', 'total_harga', 'status_bayar', 'created_at', 'pangkalan2_id', 'selesai_antar', 'status_create_briva')
            ->with([
                'pangkalan2:id,name',
            ])
            ->where('pangkalan2_id', $pangkalan_id)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalTabung = $penjualan->sum('jumlah_tabung');
        $data2['totalTabung'] = $totalTabung;

        return response()->json([
            'status' => true,
            'data' => $penjualan,
            'data2' => $data2,

        ], 202);
    }

    public function cek_status_pangkalan(Request $r)
    {
        $r->validate([
            'id' => 'required|numeric',
        ]);

        $penjualan = APenjualan::select('id', 'jumlah_tabung', 'total_harga', 'status_bayar', 'created_at', 'pangkalan2_id', 'selesai_antar', 'status_create_briva', 'keterangan')
            ->where('status_bayar', "N")
            ->where('id', $r->id)
            ->first();


        return response()->json([
            'status' => true,
            'pesan' => 'Sistem sedang dalam pengembangan.',

        ], 202);
    }
}
