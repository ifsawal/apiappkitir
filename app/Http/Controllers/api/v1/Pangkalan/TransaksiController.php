<?php

namespace App\Http\Controllers\api\v1\Pangkalan;

use Illuminate\Http\Request;
use App\Models\KitirPenjualan;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\v1\Pangkalan\TransaksiResource;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    public function getTransaksi($bulan, $tahun)
    {
        $user = Auth::user();

        $trx = KitirPenjualan::with('kitir_penjualan_briva')
            ->where('id_pang', $user->pangkalan_id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => TransaksiResource::collection($trx),

        ], 202);
    }
}
