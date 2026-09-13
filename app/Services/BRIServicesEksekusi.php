<?php

namespace App\Services;

use App\Exceptions\GagalE;
use App\Models\ABriva;
use App\Models\APenjualan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;



class BRIServicesEksekusi
{

    private BRIServices $bri;
    private BRIResponService $respon_b;
    public function __construct(BRIServices $bri, BRIResponService $respon_b)
    {
        $this->bri = $bri;
        $this->respon_b = $respon_b;
    }


    public function cekStatusBriva($id)
    {
        $data = APenjualan::with('pangkalan2', 'pangkalan2.pangkalan')->where('id', $id)->first();
        if ($data->selesai_antar == null) return throw new GagalE('Status sebagai draf...');
        if ($data->status_create_briva == null) throw new GagalE('Status, belum ditagih...');
        if ($data->status_bayar == "Y") return throw new GagalE('Status sudah dibayar...');


        $briva = $this->bri->status($data->pangkalan2->pangkalan->no_briva, 'c555' . $data->id);
        $respon = $this->respon_b->respon_briva($briva);

        if ($respon->additionalInfo->paidStatus == "Y") {

            DB::transaction(function () use ($id, $data) {
                $update = APenjualan::where('id', $id)->first();
                $update->status_bayar = "Y";
                $update->metode_bayar = "Transfer VA";
                $update->sumber_lunas = "status";
                $update->save();

                $hapus_briva = $this->bri->deleteVA($data->pangkalan2->pangkalan->no_briva);
                $respon = $this->respon_b->respon_briva($hapus_briva);
                if ($respon->responseCode != "2003100") {
                    throw new GagalE('Gagal menghapus VA');
                }
            });
        }
        return $respon->additionalInfo->paidStatus;
    }
    public function prosesReport($tanggal)
    {
        $briva = $this->bri->laporan($tanggal);
        $respon = $this->respon_b->respon_briva($briva);

        //kumpulkan kode dari report
        $kodeTransaksi = collect($respon->virtualAccountData)
            ->map(function ($item) {
                return hash('sha256', trim($item->virtualAccountNo) . '|' . $item->totalAmount->value . '|' . $item->trxDateTime);
            })->all();
        //cek apakah kode sudah ada di database
        $ambilData = ABriva::whereIn('kode_transaksi', $kodeTransaksi)->get();
        $trxSudahAda = $ambilData->pluck('kode_transaksi')->flip();

        $database = [];
        foreach ($respon->virtualAccountData as $item) {
            $kode = hash('sha256', trim($item->virtualAccountNo) . '|' . $item->totalAmount->value . '|' . $item->trxDateTime);
            //kalo udh ada, skip
            if ($trxSudahAda->has($kode)) {
                $dataLama = $ambilData->firstWhere('kode_transaksi', $kode);

                $database[] = [
                    'status' => "<font color=#006400>Tersimpan</font>",
                    'penjualan_id' => $dataLama->penjualan_id,
                ];
                continue;
            }

            DB::transaction(function () use ($item, $kode,&$database) {
                //cek apakah ada penjualan yang statusnya N
                $penjualan = APenjualan::whereHas('pangkalan2.pangkalan', function ($q) use ($item) {
                    $q->where('no_briva', $item->customerNo); //metode update status melalui data report
                })
                    ->where('status_create_briva', 1)
                    ->where('status_bayar', "N")
                    ->where('total_harga', (int) $item->totalAmount->value)
                    ->first();
                $penjualanY = APenjualan::whereHas('pangkalan2.pangkalan', function ($q) use ($item) {
                    $q->where('no_briva', $item->customerNo);  //cek yang sudah bayar (sudah cek status), tapi belum di update tgl trx nya //agar bisa di ambil dari report
                })
                    ->where('status_create_briva', 1)
                    ->where('status_bayar', "Y")
                    ->where('tanggal_tf', NULL)
                    ->where('sumber_lunas', "status")
                    ->where('total_harga', (int) $item->totalAmount->value)
                    ->orderBy('created_at', 'asc')
                    ->first();
                //cek apakah ada penjualan yang statusnya Y
                if ($penjualan) {
                    $penjualan->status_bayar = "Y";
                    $penjualan->metode_bayar = "Transfer VA";
                    $penjualan->sumber_lunas = "report";
                    $penjualan->tanggal_tf = $item->trxDateTime;
                    $penjualan->save();
                    try {
                        $hapus_briva = $this->bri->deleteVA($item->customerNo);
                        $responDelete = $this->respon_b->respon_briva($hapus_briva);
                        if ($responDelete->responseCode != "2003100") {
                            Log::channel('bri')->info('Gagal Hapus BRIVA', [
                                'Report bermasalah '        => $item->customerNo . "-" . $item->virtualAccountName . "-" . $item->totalAmount->value . " BRIVA Gagal di hapus",
                            ]);
                        }
                    } catch (\Throwable $e) {
                    }
                    $this->SimpanAbriva($penjualan, $item, $kode); //proses simpan data beriva
                    $database[] = ['status' => "<font color=#006400><b>Sukses update dari report</b></font>", 'penjualan_id' => $penjualan->id];
                } else if ($penjualanY) { // ini jika cek status manual melalui status atua aplikasi pangkalan
                    $penjualanY->tanggal_tf = $item->trxDateTime;
                    $penjualanY->save();
                    $this->SimpanAbriva($penjualanY, $item, $kode); //proses simpan data beriva
                    $database[] = ['status' => "<font color=#006400><b>Sukses update dari status></b></font>", 'penjualan_id' => $penjualanY->id];
                } else { //ini jika ada transaksi di report tapi tidak ada di database
                    $data =  (object)['id' => NULL];
                    $this->simpanABriva($data, $item, $kode);
                    // throw new GagalE($item->customerNo . "-" . $item->virtualAccountName . "-" . $item->totalAmount->value . " Tidak ditemukan", 400);
                    $database[] = ['status' => "update tidak terdeteksi di DB", 'penjualan_id' => "NULL"];
                }
            });
            
        }
        $respon = [
            "databriva" => $respon->virtualAccountData,
            "database" => $database,
        ];
        return $respon;
    }

    public function simpanABriva($data, $item, $kode)
    {
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
        $briva->penjualan_id = $data->id ?? null;
        $briva->save();
    }
}
