<?php

namespace App\Http\Controllers\api\v1\Pangkalan;

use App\Helpers\R;
use App\Http\Controllers\Controller;
use App\Models\APenjualan;
use App\Services\BRIServicesEksekusi;
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

    public function cek_status_pangkalan(Request $r, BRIServicesEksekusi $briEksekusi)
    {
        $r->validate([
            'id' => 'required|numeric|exists:a_penjualans,id',
        ]);

        $penjualan = APenjualan::select('id', 'jumlah_tabung', 'total_harga', 'status_bayar', 'created_at', 'pangkalan2_id', 'selesai_antar', 'status_create_briva', 'keterangan')
            ->where('id', $r->id)
            ->first();

        if ($penjualan->status_bayar == 'Y') return R::gagal('Sudah dibayar');


        $respon = $briEksekusi->cekStatusBriva($r->id);

        return response()->json([
            'status' => true,
            'pesan' => "Status Pembayaran Anda : " . $respon,

        ], 202);
    }
}
