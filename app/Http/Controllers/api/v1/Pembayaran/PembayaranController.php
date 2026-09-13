<?php

namespace App\Http\Controllers\api\v1\Pembayaran;

use App\Exceptions\GagalE;
use App\Helpers\R;
use App\Http\Controllers\Controller;
use App\Models\APenjualan;
use App\Services\BRIResponService;
use App\Services\BRIServices;
use Carbon\Carbon;
use Illuminate\Http\Request;



class PembayaranController extends Controller
{
    public function index(BRIServices $service)
    {
        // return $service->getToken();
        // return $service->create("554507","iwan 34", "c4456", "20000");
        return $service->transferVA('554498111', "Jokul Doe", "001901000032531", "abcdefgh1234", "100000.00");
        return $service->updateStatusVA("99925", "c555127", "Y");
        // return $service->updateVA("554492","Sawal 8", "uv4456", "10245","peruhan ke 3");
        // return $service->inquiryVA("99956", "c555121");
        // return $service->deleteVA("99925");
        return $service->status("99956", "c555121");
        return $service->laporan("2026-09-07");
    }

    public function tes_transfer(Request $r, BRIServices $service, BRIResponService $brivaResponse)
    {
        
        $r->validate([
            'id_penjualan' => 'required|numeric|exists:a_penjualans,id',
        ]);

        if(!app()->isLocal()) throw new GagalE('Gagal, Akses hanya bisa di lakukan di local', 400);
        $data = APenjualan::with('pangkalan2', 'pangkalan2.pangkalan')->where('id', $r->id_penjualan)->first();

        $create = $service->transferVA($data->pangkalan2->pangkalan->no_briva, $data->pangkalan2->name, "001901000032531", "c555" . $r->id_penjualan, $data->total_harga . ".00");
        $update = $service->updateStatusVA($data->pangkalan2->pangkalan->no_briva, "c555" . $r->id_penjualan, "Y");
        return response()->json([
            'status' => 'sukses',
            'data' => $brivaResponse->respon_briva($create),
            'update' => $update->respon_briva($update),
        ]);
    }

    public function status(Request $r, BRIServices $service, BRIResponService $brivaResponse)
    {
        $r->validate([
            'briva_no' => 'required|numeric',
            'inquiryRequestId' => 'required|string',
        ]);
        $briva = $service->status($r->briva_no, $r->inquiryRequestId);
        $briva = $brivaResponse->respon_briva($briva);
        return R::data('Data ditemukan', $briva->virtualAccountData);
    }


    public function delete(Request $r, BRIServices $service, BRIResponService $brivaResponse)
    {
        $r->validate([
            'briva_no' => 'required|numeric',
        ]);
        $briva = $service->deleteVA($r->briva_no);
        $briva = $brivaResponse->respon_briva($briva);
        return R::sukses('Sukses terhapus...');
    }
    public function create(Request $r, BRIServices $service, BRIResponService $brivaResponse)
    {
        $r->validate([
            'briva_no' => 'required|numeric',
            'nama' => 'required|string',
            'transaksi_id' => 'required|string',
            'jumlah' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        $briva = $service->create($r->briva_no, $r->nama, $r->transaksi_id, $r->jumlah, $r->keterangan);
        $briva = $brivaResponse->respon_briva($briva);
        return R::sukses('Sukses');
    }


    public function report(Request $r, BRIServices $service, BRIResponService $brivaResponse)
    {
        $r->validate([
            'tanggal' => 'required|date:Y-m-d'
        ]);
        $formattedDate = Carbon::parse($r->tanggal)->format('Y-m-d');
        $r->merge(['tanggal' => $formattedDate]);

        $briva = $service->laporan($r->tanggal);
        $briva = $brivaResponse->respon_briva($briva);
        return R::data('Data ditemukan', $briva->virtualAccountData);
    }

    public function inquiry(Request $r, BRIServices $service, BRIResponService $brivaResponse)
    {
        $r->validate([
            'briva_no' => 'required|numeric',
            'transaksi_id' => 'required|string'
        ]);

        $briva = $service->inquiryVA($r->briva_no, $r->transaksi_id);
        $briva = $brivaResponse->respon_briva($briva);
        return R::data('Data ditemukan', $briva->virtualAccountData);
    }
}
