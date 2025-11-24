<?php

namespace App\Http\Controllers\api\v1\Kitir;


use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Kitir;
use Dotenv\Validator;
use App\Models\Seting;
use App\Models\KitirPecah;
use Illuminate\Http\Request;
use App\Models\KitirPenjualan;
use PhpParser\Node\Stmt\TryCatch;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Api\v1\Kitir\KitirResource;

class KitirController extends Controller
{
    public function kitir(Request $r, $tanggal)
    {
        $tanggal = date('Y-m-d', strtotime($tanggal));
        $r->merge(['tanggal' => $tanggal]);
        $r->validate([
            'tanggal' => 'required|date_format:Y-m-d',
        ]);

        // $kitir=Kitir::with('kitir_pecah:kitir_penjualan_id,id_k,jumlah', 'kitir_pecah.kitir_penjualan:id,id_pang,tanggal,jumlah', 'kitir_pecah.kitir_penjualan.pangkalan:id_pang,nama');
        // $kitir->join('pangkalan', 'kitir.id_pang', '=', 'pangkalan.id_pang');
        // $kitir->join('bagi_pangkalan', 'kitir.id_k', '=', 'bagi_pangkalan.id_k');
        // $kitir->join('user', 'bagi_pangkalan.id_u', '=', 'user.id_u');
        // $kitir->where('tgl_masuk', $tanggal);
        // return $kitir = $kitir->get();

        $kitir = Kitir::with('pangkalan:id_pang,nama', 'bagi_pangkalan:id_u,id_k', 'bagi_pangkalan.user:id_u,user', 'kitir_pecah:kitir_penjualan_id,id_k,jumlah', 'kitir_pecah.kitir_penjualan:id,id_pang,tanggal,jumlah', 'kitir_pecah.kitir_penjualan.kitir_penjualan_briva:id_briva,kitir_penjualan_ID,jumlah_bayar,status_bayar', 'kitir_pecah.kitir_penjualan.pangkalan:id_pang,nama')
            ->where('tgl_masuk', $tanggal)
            ->get();


        $tgl_kemarin = Carbon::parse($tanggal);
        $kemarin = $tgl_kemarin->subDay()->toDateString();
        $tgl_besok = Carbon::parse($tanggal);
        $besok = $tgl_besok->addDay()->toDateString();
        $hari = Carbon::parse($tanggal)->locale('id')->isoFormat('dddd');


        // $a = KitirResource::collection($kitir);
        return response()->json([
            'status' => true,
            'data' => $kitir,
            'kemarin' => $kemarin,
            'besok' => $besok,
            'sekarang' => date('d-m-Y', strtotime($tanggal)),
            'hari' => $hari,
        ], 202);
    }

    public function jual(Request $r)
    {

        $user = Auth::user();

        $r->validate([
            'id_k' => 'required|numeric',
            'id_pang' => 'required|numeric',
            'jumlah' => 'required|numeric',
        ]);

        $terjual = KitirPecah::where('id_k', $r->id_k)->sum('jumlah');
        $stok = Kitir::where('id_k', $r->id_k)->first();
        if (($terjual + $r->jumlah) > $stok->jumlah) {
            return response()->json([
                'status' => false,
                'pesan' => "Stok tidak mencukupi. Sisa stok " . ($stok->jumlah - $terjual) . " tabung...",
            ], 422);
        }

        $seting = Seting::where('nama', 'harga_lpg3kg')->first();

        try {
            DB::beginTransaction();

            //simpan penjualan

            $kitir_penjualan = new KitirPenjualan();
            $kitir_penjualan->id_pang = $r->id_pang;
            $kitir_penjualan->tanggal = date('Y-m-d H:i:s');
            $kitir_penjualan->jumlah = $r->jumlah;
            $kitir_penjualan->status = 1;
            $kitir_penjualan->user_id = $user->id;
            $kitir_penjualan->notif = 0;
            $kitir_penjualan->sis_bayar = "";
            $kitir_penjualan->harga = $seting->nilai * $r->jumlah;

            $kitir_penjualan->save();

            $kitir_pecah = new KitirPecah();
            $kitir_pecah->id_k = $r->id_k;
            $kitir_pecah->kitir_penjualan_id = $kitir_penjualan->id;
            $kitir_pecah->jumlah = $r->jumlah;
            $kitir_pecah->save();
            // DB::rollBack();
            DB::commit();
            return response()->json([
                'status' => true,
                'pesan' => "Data berhasil disimpan...",
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'pesan' => "Data gagal disimpan. " . $e->getMessage(),
            ], 500);
        }


        return response()->json([
            'status' => true,
            'pesan' => "Data berhasil disimpan...",
        ], 202);
    }

    public function jual_tambah(Request $r)
    {
        $user = Auth::user();


        $r->validate([
            'id_penjualan' => 'required|numeric|exists:kitir_penjualan,id',
            'id_k' => 'required|numeric',
            'id_pang' => 'required|numeric',
            'jumlah' => 'required|numeric',
        ]);


        $cek = KitirPenjualan::findOrFail($r->id_penjualan);
        if ($cek->user_id_u != $user->id_u) {
            return response()->json([
                'status' => false,
                'pesan' => "Yang menambahkan harus akun yang sama dengan penjualan...",
            ], 422);
        }

        $pecah= KitirPecah::where('kitir_penjualan_id', $r->id_penjualan)->where('id_k', $r->id_k)->first();
        if ($pecah) {
            return response()->json([
                'status' => false,
                'pesan' => "Kekurangan harus di ambil dari kitir yang lain, atau di rubah...",
            ], 422);
        }

        $otal = $cek->jumlah + $r->jumlah;
        $seting = Seting::where('nama', 'harga_lpg3kg')->first();

        try {
            DB::beginTransaction();
            $kitir_pecah = new KitirPecah();
            $kitir_pecah->id_k = $r->id_k;
            $kitir_pecah->kitir_penjualan_id = $r->id_penjualan;
            $kitir_pecah->jumlah = $r->jumlah;
            $kitir_pecah->save();

            $cek->jumlah = $otal;
            $cek->harga = $cek->harga + ($seting->nilai * $r->jumlah);
            $cek->save();

            // DB::rollBack();
            DB::commit();
            return response()->json([
                'status' => true,
                'pesan' => "Data berhasil disimpan...",
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'pesan' => "Data gagal disimpan. " . $e->getMessage(),
            ], 500);
        }
    }
}
