<?php

namespace App\Http\Controllers\api\v1\Pangkalan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DasboardController extends Controller
{
    public function index()
    {
        // return response()
        //     ->json([
        //         'sukses' => true,
        //         'pesan' => "ANDA BELUM MEMBAYAR, MOHON SEGERA TUTUP AKSES",
        //     ], 403);

            return response()
            ->json([
                'sukses' => true,
                'pesan' => "akses sukses",
                'akses_iklan' => "setiap_waktu--",
                'tanggal' => date("Y-m-d"),
                'gambar'=> [
                    "https://api.appkitir.com/gambar/Aplikasi-Kitir-V-1.jpg",
                    "https://img.okezone.com/infografis/2022/12/27/559/778430/cara-beli-lpg-3-kg-pakai-ktp-di-2023_cQk2YrfRJo.jpg",
                ]
            ], 202);
    }
}
