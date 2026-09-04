<?php

namespace App\Http\Controllers\api\v1\Pembayaran;

use App\Helpers\R;
use App\Http\Controllers\Controller;
use App\Services\BRIResponService;
use App\Services\BRIServices;
use Carbon\Carbon;
use Illuminate\Http\Request;



class PembayaranController extends Controller
{
    public function index(BRIServices $service)
    {
        // return $service->getToken();
        return $service->create("554506","iwan 34", "c4455", "2");
        // return $service->updateVA("554492","Sawal 8", "uv4456", "10245","peruhan ke 3");
        // return $service->updateStatusVA("554492", "us4455", "Y");
        return $service->inquiryVA("55450645", "c4455");
        // return $service->deleteVA("554506");
        return $service->status("554495", "11232");
        // return $service->status("99917", "11232");
        return $service->laporan("2026-08-18");
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
