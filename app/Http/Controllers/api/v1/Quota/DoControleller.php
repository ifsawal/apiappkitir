<?php

namespace App\Http\Controllers\api\v1\Quota;

use App\Http\Controllers\Controller;
use App\Models\AArmada;
use App\Models\ADo;
use App\Models\AQuota;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DoControleller extends Controller
{
    public function buat_do(Request $r)
    {
        $r->validate([
            'quota_id' => 'required|numeric',
            'tanggal_sampai' => 'required|date:Y-m-d',
            'supir_id' => 'required|numeric',
        ]);
        $a = AArmada::where('supir_user_id', $r->supir_id)->first();
        $q = AQuota::where('id', $r->quota_id)->first();
        $supir = User::where('id', $r->supir_id)->first(); //ini hanya berlaku bila armada hanya punya supir tunggal tidak doble
        $cek_do = ADo::where('quota_id', $r->quota_id)->sum('jumlah');
        $do_input = $cek_do + ($r->jumlah ?? $q->quota); //do tersimpan + input

        if ($cek_do > 0 and $do_input >= $q->quota) return response()->json(['status' => false, 'pesan' => 'DO melebihi quota.'], 404);


        $do = new ADo();

        $do->quota_id = $q->id;
        $do->tanggal_muat = $r->tanggal_muat ?? Carbon::parse($r->tanggal_sampai)->subDay()->format('Y-m-d');
        $do->tanggal_sampai = $r->tanggal_sampai;
        $do->tujuan = $r->tujuan;
        $do->jumlah = $r->jumlah ?? $q->quota;
        $do->keterangan = $q->keterangan;
        $do->mobil = $r->mobil ?? $supir->name;
        $do->supir_user_id = $r->supir_id;
        $do->armada_id = $a->id;
        $do->input_user_id = auth()->user()->id;
        $do->save();

        return response()->json(['status' => true, 'pesan' => 'Data berhasil disimpan.'], 201);
    }


    public function ambil_do(Request $r)
    {
        $r->validate([
            'tanggal' => 'required|date:Y-m-d',
        ]);

        $do = ADo::with('armada:id,plat,nama')
            ->withSum('penjualan_detil as terjual', 'jumlah_tabung')
            ->whereYear('tanggal_sampai', date('Y', strtotime($r->tanggal)))
            ->whereMonth('tanggal_sampai', date('m', strtotime($r->tanggal)))
            ->orderBy('tanggal_sampai', 'desc')
            ->get();
        $do->map(function ($item) {
            $item->hari_sampai = Carbon::parse($item->tanggal_sampai)->translatedFormat('l');
            $item->terjual ??= 0;
            $item->sisa = $item->jumlah - $item->terjual;
            return $item;
        });



        $tgl = [
            'tanggal_hari_ini' => date('Y-m-d'),
            'tanggal' => $r->tanggal,
            'tanggal_format' => Carbon::parse($r->tanggal)->format('d-m-Y'),
            'tanggal_kemarin' => Carbon::parse($r->tanggal)->subDay()->toDateString(),
            'tanggal_besok' => Carbon::parse($r->tanggal)->addDay()->toDateString(),
        ];
        return response()->json([
            'status' => true,
            'pesan' => 'Data berhasil diambil.',
            'data' => $do,
            'tgl' => $tgl
        ], 202);
    }
}
