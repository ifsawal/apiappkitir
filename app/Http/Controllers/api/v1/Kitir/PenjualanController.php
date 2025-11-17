<?php

namespace App\Http\Controllers\api\v1\Kitir;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\KitirPenjualan;
use App\Http\Controllers\Controller;

class PenjualanController extends Controller
{
        public function penjualan(Request $r, $tanggal)
    {
        $tanggal = date('Y-m-d', strtotime($tanggal));
        $r->merge(['tanggal' => $tanggal]);
        $r->validate([
            'tanggal' => 'required|date_format:Y-m-d',
        ]);


        $tgl_kemarin = Carbon::parse($tanggal);
        $kemarin = $tgl_kemarin->subDay()->toDateString();
        $tgl_besok = Carbon::parse($tanggal);
        $besok = $tgl_besok->addDay()->toDateString();
        $hari = Carbon::parse($tanggal)->locale('id')->isoFormat('dddd');


        return  $penjualan = KitirPenjualan::with('pangkalan:id_pang,nama', 'kitir_pecah:kitir_penjualan_id,id_k,jumlah', 'kitir_pecah.kitir_penjualan:id,id_pang,tanggal,jumlah', 'kitir_pecah.kitir_penjualan.kitir_penjualan_briva:id_briva,kitir_penjualan_ID,jumlah_bayar,status_bayar', 'kitir_pecah.kitir_penjualan.pangkalan:id_pang,nama')
            ->where('tanggal', $tanggal)
            ->get();


    }
}
