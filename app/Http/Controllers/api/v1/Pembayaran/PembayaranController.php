<?php

namespace App\Http\Controllers\api\v1\Pembayaran;

use App\Http\Controllers\Controller;
use App\Services\BRIServices;


class PembayaranController extends Controller
{
    public function index(BRIServices $service)
    {
        // return $service->getToken();
        // return $service->create("554506","iwan 34", "c4455", "50000");
        // return $service->updateVA("554492","Sawal 8", "uv4456", "10245","peruhan ke 3");
        // return $service->updateStatusVA("554492", "us4455", "Y");
        // return $service->inquiryVA("99971", "in4455");
        // return $service->deleteVA("554506");
        // return $service->status("554495", "11232");
        // return $service->status("99917", "11232");
        return $service->laporan("2026-08-18");
    }
}
