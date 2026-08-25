<?php

namespace App\Http\Controllers\api\v1\Penjualan;

use App\Exceptions\GagalE;
use App\Helpers\R;
use App\Http\Controllers\Controller;
use App\Models\ABriva;
use App\Models\ADo;
use App\Models\APenjualan;
use App\Models\APenjualanDetil;
use App\Models\ASeting;
use App\Models\Pangkalan2;
use App\Services\BRIResponService;
use App\Services\BRIServices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class PenjualanController extends Controller
{

    public function __construct(private BRIResponService $brivaResponse) {}

    public function respon_briva($briva)
    {
        return $this->brivaResponse->respon_briva($briva);
    }
    public function simpan_penjualan(Request $r, BRIServices $bri)
    {
        $r->validate([
            'pangkalan_id' => 'required|numeric|exists:pangkalan2s,id',
            'jumlah' => 'required|numeric',
            'do_id' => 'required|numeric|exists:a_dos,id',
        ]);
        $user_id = auth()->user()->id;



        return DB::transaction(function () use ($r, $user_id, $bri) {

            $seting = ASeting::whereIn('key', ['harga'])->pluck('value', 'key');
            // $seting['harga'];
            !isset($seting['harga']) ?? throw ValidationException::withMessages(['harga' => 'Harga belum di set!']);

            $do = ADo::findOrFail($r->do_id);
            $hitung_penjualan = APenjualanDetil::where('do_id', $r->do_id)->sum('jumlah_tabung');
            $hitung_penjualan_dan_input = $hitung_penjualan + $r->jumlah;
            $sisa = $do->jumlah - $hitung_penjualan;
            if ($hitung_penjualan == $do->jumlah) {
                $do->status = 'selesai';
                $do->save();
            }

            if ($hitung_penjualan == $do->jumlah) throw new GagalE('DO / Kitir habis', 400);
            if ($hitung_penjualan_dan_input > $do->jumlah) throw new GagalE('DO / Kitir tidak cukup .' . $sisa . ' tabung tersisa.', 400);

            $pesan_briva = "";
            if (isset($r->tambahan)) { //tambahan itu isinya id _penjualan yang mau di tambah
                $r->validate([
                    'tambahan' => 'required|numeric|exists:a_penjualans,id',
                ]);
                $a = APenjualan::findOrFail($r->tambahan);
                if ($a->selesai_antar == 1) throw new GagalE('Penjualan sudah diantar', 400); //return R::gagal('Penjualan sudah diantar');
                if ($a->status_bayar == "Y") throw new GagalE('Pembayaran sudah di lakukan', 400); //return R::gagal('Pembayaran sudah di lakukan');

                $a->jumlah_tabung = $a->jumlah_tabung + $r->jumlah;
                $a->total_harga = $a->total_harga + ($r->jumlah * $seting['harga']);
                $a->save();

                $detil = APenjualanDetil::where('penjualan_id', $a->id)->where('do_id', $r->do_id)->first(); //mengecek apakah sudah ada input detil
                if ($detil) {
                    $detil->jumlah_tabung = (+$detil->jumlah_tabung + $r->jumlah);
                    $detil->input_user_id = $user_id;
                    $detil->save();
                    return R::sukses("Sukses menambah");
                }
            } else {
                $a = new APenjualan();
                $a->pangkalan2_id = $r->pangkalan_id;
                $a->jumlah_tabung = $r->jumlah;
                $a->total_harga = $r->jumlah * $seting['harga'];
                $a->keterangan = $r->keterangan;
                if (isset($r->selesai_antar)) $a->selesai_antar = 1;
                if (isset($r->briva)) {
                    $a->status_create_briva = 1;
                    $a->selesai_antar = 1;
                }
                $a->save();

                if (isset($r->briva)) {

                    $penjualan = APenjualan::where('pangkalan2_id', $a->pangkalan2_id)
                        ->where('status_bayar', "N")
                        ->get();
                    // if nya >1 karena sudah di input duluan di atas
                    if ($penjualan->count() > 1) throw new GagalE('Jangan contreng Tagih karena ada Penjualan yang belum dibayar', 400);

                    $pangkalan = Pangkalan2::with('pangkalan')->where('pangkalan_id', $a->pangkalan2_id)->first();
                    $briva = $bri->create($pangkalan->pangkalan->no_briva, $pangkalan->name, "c555" . $a->id, $a->total_harga, "LPG 3 Kg sejumlah " . $a->jumlah_tabung);
                    $this->respon_briva($briva);
                    $pesan_briva = "dan sukses tertagih...";
                }
            }

            $detil = new APenjualanDetil();
            $detil->jumlah_tabung = $r->jumlah;
            $detil->do_id = $r->do_id;
            $detil->penjualan_id = $a->id;
            $detil->armada_id = $do->armada_id;
            $detil->input_user_id = $user_id;
            $detil->save();
            return R::sukses('Sukses disimpan.' . $pesan_briva);
        });
    }


    public function hapus_penjualan(Request $r)
    {
        $r->validate([
            'id' => 'required|numeric|exists:a_penjualans,id',
        ]);
        $cek = APenjualan::findOrFail($r->id);
        if ($cek->selesai_antar == 1) return R::gagal('Gagal... Penjualan sudah diantar');
        if ($cek->status_bayar == "Y") return R::gagal('Gagal...Pembayaran sudah di lakukan');
        // if (date('Y-m-d') <> $cek->created_at->format('Y-m-d')) return R::gagal('Penjualan tanggal lain tidak bisa di hapus lagi', 400);
        if ($cek->created_at->toDateString() !== now()->toDateString() && !auth()->user()->can('hapus penjualan')) {
            return R::gagal('Penjualan tanggal lain tidak bisa dihapus lagi.', 400);
        }
        return DB::transaction(function () use ($r) {
            APenjualanDetil::where('penjualan_id', $r->id)->delete();
            APenjualan::where('id', $r->id)->delete();
            return R::sukses('Sukses dihapus.', 204);
        });
    }

    public function selesai_antar(Request $r)
    {
        $r->validate([
            'id' => 'required|numeric|exists:a_penjualans,id',
        ]);

        $update = APenjualan::where('id', $r->id)->first();
        if ($update->selesai_antar == 1) return R::gagal('Penjualan sudah diantar');
        $update->selesai_antar = 1;
        $update->save();
        return R::sukses('Sukses diselesai antar.', 201);
    }
    public function batal_antar(Request $r)
    {
        $r->validate([
            'id' => 'required|numeric|exists:a_penjualans,id',
        ]);

        $update = APenjualan::where('id', $r->id)->first();
        if ($update->selesai_antar == null) return R::gagal('Penjualan belum diantar');
        if ($update->status_create_briva == 1) return R::gagal('Gagal, karena karena sudah tertagih...');
        if ($update->created_at->toDateString() !== now()->toDateString() && !auth()->user()->can('batal antar')) {
            return R::gagal('Penjualan tanggal lain tidak bisa dibatalkan lagi.', 400);
        }
        $update->selesai_antar = null;
        $update->save();
        return R::sukses('Pengantaran LPG dibatalkan', 201);
    }








    public function tagih(Request $r, BRIServices $bri)
    {
        $r->validate([
            'id' => 'required|numeric|exists:a_penjualans,id',
        ]);
        return DB::transaction(function () use ($r, $bri) {
            // $cek = APenjualan::where('status_bayar', 'N')->get();
            // if($cek->count() > 0) throw new GagalE('Gagal, masih ada penjualan yang belum dibayar...', 404);
            $data = APenjualan::with('pangkalan2', 'pangkalan2.pangkalan')->where('id', $r->id)->first();
            if ($data->selesai_antar == null) throw new GagalE('Gagal, status belum diantar...', 400);
            if ($data->status_create_briva == 1) throw new GagalE('Gagal, karena karena sudah tertagih...', 400);
            $briva = $bri->create($data->pangkalan2->pangkalan->no_briva, $data->pangkalan2->name, "c555" . $data->id, $data->total_harga, "LPG 3 Kg sejumlah " . $data->jumlah_tabung);
            $this->respon_briva($briva); //PROSES CREATE BRIVA
            $update = APenjualan::where('id', $r->id)->first();
            $update->status_create_briva = 1;
            $update->save();
            return R::sukses('Sukses tertagih.', 201);
        });
    }

    public function batal_tagih(Request $r, BRIServices $bri)
    {
        $r->validate([
            'id' => 'required|numeric|exists:a_penjualans,id',
        ]);
        return DB::transaction(function () use ($r, $bri) {
            $data = APenjualan::with('pangkalan2', 'pangkalan2.pangkalan')->where('id', $r->id)->first();
            if ($data->status_create_briva == null) throw new GagalE('Belum di tagih', 400);
            if ($data->status_bayar == "Y") throw new GagalE('Gagal, karena sudah dibayar...', 400);
            // $briva = $bri->status($data->pangkalan2->pangkalan->no_briva, 'c555' . $data->id);


            $briva =  $bri->deleteVA($data->pangkalan2->pangkalan->no_briva);
            $this->respon_briva($briva); //PROSES CREATE BRIVA
            $update = APenjualan::where('id', $r->id)->first();
            $update->status_create_briva = null;
            $update->save();
            return R::sukses('Pembatalan sukses.', 201);
        });
    }


    public function status_tagihan(Request $r, BRIServices $bri)
    {
        $r->validate([
            'id' => 'required|numeric|exists:a_penjualans,id',
        ]);
        $data = APenjualan::with('pangkalan2', 'pangkalan2.pangkalan')->where('id', $r->id)->first();
        if ($data->selesai_antar == null) return R::gagal('Status sebagai draf...');
        if ($data->status_create_briva == null) return R::gagal('Status, belum ditagih...');
        // if ($data->status_bayar == "Y") return R::gagal('Status DB sudah dibayar...');
        $briva = $bri->status($data->pangkalan2->pangkalan->no_briva, 'c555' . $data->id);
        // $briva = $bri->status("554495", 'c555' . $data->id);
        $respon = $this->respon_briva($briva);
        return R::sukses("Status pembayaran : " . $respon->additionalInfo->paidStatus, 202);
    }

    public function status_create(Request $r, BRIServices $bri)
    {
        $r->validate([
            'id' => 'required|numeric|exists:a_penjualans,id',
        ]);
        $data = APenjualan::with('pangkalan2', 'pangkalan2.pangkalan')->where('id', $r->id)->first();
        $briva = $bri->inquiryVA($data->pangkalan2->pangkalan->no_briva, 'c555' . $data->id);
        $respon = $this->respon_briva($briva);
        return R::sukses(json_encode($respon),  202);
    }



    public function informasi_umum(Request $r)
    {
        $r->validate([
            'id' => 'required|numeric|exists:pangkalan2s,id',
        ]);
        $data = Pangkalan2::with('pangkalan')->where('id', $r->id)->first();
        $data->pangkalan->no_briva = config('pembayaran.patner_id') . $data->pangkalan->no_briva;
        return response()->json([
            'status' => true,
            'data' => $data,
        ], 202);
    }

    public function cek_kewajiban(Request $r)
    {
        $r->validate([
            'id' => 'required|numeric|exists:pangkalan2s,id',
        ]);

        $penjualan = APenjualan::select('id', 'jumlah_tabung', 'total_harga', 'status_bayar', 'created_at', 'pangkalan2_id', 'selesai_antar', 'status_create_briva', 'keterangan')
            ->where('status_bayar', "N")
            ->where('pangkalan2_id', $r->id)
            ->orderBy('created_at', 'desc')->get();
        $jumlah = $penjualan->count();
        $pesan = "";
        if ($jumlah > 0) {
            $pesan = "Ada $jumlah penjualan belum dibayar";
        }
        return response()->json([
            'status' => true,
            'pesan' => $pesan,
        ], 202);
    }

    public function daftar_transfer(Request $r, BRIServices $bri)
    {

        $r->merge([
            'tanggal' => \Carbon\Carbon::parse($r->tanggal)->format('Y-m-d')
        ]);
        $r->validate([
            'tanggal' => 'required|date:Y-m-d'
        ]);

        $briva = $bri->laporan($r->tanggal);
        $respon = $this->respon_briva($briva);

        $kodeTransaksi = collect($respon->virtualAccountData)
            ->map(function ($item) {
                return hash('sha256', trim($item->virtualAccountNo) . '|' . $item->totalAmount->value . '|' . $item->trxDateTime);
            })->all();
        $trxSudahAda = ABriva::whereIn('kode_transaksi', $kodeTransaksi)->pluck('kode_transaksi')->flip();


        foreach ($respon->virtualAccountData as $item) {
            $kode = hash('sha256', trim($item->virtualAccountNo) . '|' . $item->totalAmount->value . '|' . $item->trxDateTime);
            if ($trxSudahAda->has($kode)) {
                continue;
            }

            DB::transaction(function () use ($item, $kode, $bri) {

                $penjualan = APenjualan::whereHas('pangkalan2.pangkalan', function ($q) use ($item) {
                    $q->where('no_briva', $item->customerNo);
                })
                    ->where('status_create_briva', 1)
                    ->where('status_bayar', "N")
                    ->where('total_harga', (int) $item->totalAmount->value)
                    ->get();

                if ($penjualan->count() === 1) {
                    $data = $penjualan->first();
                    $data->status_bayar = "Y";
                    $data->metode_bayar = "Transfer VA";
                    $data->save();
                } else {
                    throw new GagalE($item->customerNo . "-" . $item->virtualAccountName . "-" . $item->totalAmount->value . " Tidak ditemukan", 400);
                }
                $hapus_briva = $bri->deleteVA($item->customerNo);
                $respon = $this->respon_briva($hapus_briva);
                if ($respon->responseCode != "2003100") throw new GagalE($item->customerNo . "-" . $item->virtualAccountName . "-" . $item->totalAmount->value . " BRIVA Gagal di hapus", 400);


                $briva = new ABriva();
                $briva->data = $item;
                $briva->customerNo = trim($item->customerNo);
                $briva->virtualAccountNo = trim($item->virtualAccountNo);
                $briva->value = $item->totalAmount->value;
                $briva->trxDateTime = $item->trxDateTime;
                $briva->virtualAccountName = $item->virtualAccountName;
                $briva->trxId = $item->trxId;
                $briva->description = $item->additionalInfo->description;
                $briva->sourceAccountVa = $item->additionalInfo->sourceAccountVa;
                $briva->tellerId = $item->additionalInfo->tellerId;
                $briva->kode_transaksi = $kode;
                $briva->penjualan_id = $data->id;
                $briva->save();
            });
        }


        return response()->json([
            'status' => true,
            'data' => $respon->virtualAccountData,
        ], 202);
    }
    public function penjualan_terakhir(Request $r)
    {
        $r->validate([
            'id' => 'required|numeric|exists:pangkalan2s,id',
        ]);

        $penjualan = APenjualan::select('id', 'jumlah_tabung', 'total_harga', 'status_bayar', 'created_at', 'pangkalan2_id', 'selesai_antar', 'status_create_briva', 'keterangan')
            ->where('pangkalan2_id', $r->id)
            ->with([
                'detil' => function ($q) use ($r) {
                    $q->select('id', 'penjualan_id', 'jumlah_tabung', 'do_id', 'armada_id', 'input_user_id');
                    if ($r->do_id) $q->where('do_id', $r->do_id);
                },
                'pangkalan2:id,name',
                'detil.armada:id,plat,nama',
                'detil.user:id,name'
            ])
            ->orderBy('created_at', 'desc')->limit(30)->get();
        return response()->json([
            'status' => true,
            'data' => $penjualan,
        ], 202);
    }
    public function ambil_penjualan(Request $r)
    {
        $r->validate([
            'tanggal' => 'required|date:Y-m-d',
        ]);

        // DB::listen(function ($query) {
        //     logger()->info($query->sql, $query->bindings);
        // });
        // return $r->do_id ?? null;

        $penjualan = APenjualan::select('id', 'jumlah_tabung', 'total_harga', 'status_bayar', 'created_at', 'pangkalan2_id', 'selesai_antar', 'status_create_briva','keterangan')
            ->when($r->do_id, function ($q) use ($r) {
                $q->whereHas('detil', function ($query) use ($r) {
                    $query->where('do_id', $r->do_id);
                });
            })
            ->with([
                'detil' => function ($q) use ($r) {
                    $q->select('id', 'penjualan_id', 'jumlah_tabung', 'do_id', 'armada_id', 'input_user_id');
                    if ($r->do_id) $q->where('do_id', $r->do_id);
                },
                'pangkalan2:id,name',
                'detil.armada:id,plat,nama',
                'detil.user:id,name'
            ])
            ->when(
                $r->belum_bayar,
                function ($q) use ($r) {
                    $tanggal = Carbon::parse($r->tanggal);
                    $q->where('status_bayar', 'N')
                        ->whereYear('created_at', $tanggal->year)
                        ->whereMonth('created_at', $tanggal->month);
                },
                function ($q) use ($r) {
                    $q->whereDate('created_at', $r->tanggal);
                }
            )
            ->orderBy('created_at', 'desc')->get();
        $totalTabung = $penjualan->sum('jumlah_tabung');
        $data2['totalTabung'] = $totalTabung;

        if ($r->do_id_terpilih) {
            $hitung_do_terpilih = APenjualanDetil::where('do_id', $r->do_id_terpilih)->sum('jumlah_tabung');
            $data2['penjualan_do_terpilih'] = $hitung_do_terpilih;
            $jumlah_per_do = ADo::where('id', $r->do_id_terpilih)->first()->jumlah;
            $data2['sisa_per_do'] = $jumlah_per_do - $hitung_do_terpilih;
        } else {
            $data2['penjualan_do_terpilih'] = 0;
            $data2['sisa_per_do'] = 0;
        }

        $tgl = [
            'tanggal' => $r->tanggal,
            'tanggal_format' => Carbon::parse($r->tanggal)->format('d-m-Y'),
            'tanggal_kemarin' => Carbon::parse($r->tanggal)->subDay()->toDateString(),
            'tanggal_besok' => Carbon::parse($r->tanggal)->addDay()->toDateString(),
        ];

        $pengaturan = [];
        return response()->json([
            'status' => true,
            'data' => $penjualan,
            'data2' => $data2,
            'tgl' => $tgl,
            'pengaturan' => $pengaturan,
        ], 202);
    }
}
