<?php

namespace App\Http\Controllers\api\v1\Quota;

use App\Http\Controllers\Controller;
use App\Models\APerubahan;
use App\Models\AQuota;
use Illuminate\Http\Request;

class QuotaController extends Controller
{
    public function quota_simpan(Request $r)
    {
        $r->validate([
            'tanggal' => 'required|date:Y-m-d',
            'quota' => 'required|numeric',
            'jenis' => 'required|in:N,F',
        ]);

        $q = new AQuota;
        $q->tanggal = $r->tanggal;
        $q->quota = $r->quota;
        $q->jenis = $r->jenis;
        $q->input_user_id = auth()->user()->id;
        $q->save();

        return response()->json(['status' => true, 'pesan' => 'Data berhasil disimpan.'], 201);
    }


    public function get_quota(Request $r)
    {
        $r->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:9999',
        ]);

        $data = AQuota::with('do:quota_id,tanggal_sampai,supir_user_id,jumlah', 'do.supir:id,name')
            ->whereMonth('tanggal', $r->bulan)
            ->select('id', 'tanggal', 'quota', 'jenis', 'status', 'perubahan')
            ->whereYear('tanggal', $r->tahun)
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($item) {
                return $item;
            });


        if ($data->isEmpty()) {
            return response()->json([
                'status' => false,
                'pesan' => 'Data tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'pesan' => 'Data berhasil disimpan.',
            'data' => $data,
        ], 202);
    }


    public function quota_hapus(Request $r)
    {
        $r->validate([
            'id' => 'required|integer',
        ]);


        $quota = AQuota::where('id', $r->id)->whereNull('perubahan')->first();
        if (!$quota) {
            return response()->json(['status' => false, 'pesan' => 'Menunggu persetujuan.'], 404);
        }

        $quota->perubahan = 'hapus';
        $quota->save();


        $perubahan = new APerubahan();
        $perubahan->nama_tabel = AQuota::class;
        $perubahan->record_id = $quota->id;
        $perubahan->aksi = 'hapus';
        $perubahan->data_lama = $quota->toJson();
        $perubahan->status = 'belum';
        $perubahan->input_user_id = auth()->user()->id;

        $perubahan->save();

        return response()->json(['status' => true, 'pesan' => 'Permintaan berhasil di ajukan.'], 201);
    }
}
