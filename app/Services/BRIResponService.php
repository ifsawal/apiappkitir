<?php

namespace App\Services;

use App\Exceptions\GagalE;



class BRIResponService
{

    public function respon_briva($briva)
    {
        $briva = $briva->object();
        if ((!is_object($briva))) {
            throw new GagalE("Bank Error.", 400);
        }

        $kode = $briva->responseCode ?? null;
        if ($kode && isset(config('pembayaran.briva_response.berhasil')[$kode])) {
            return $briva;
        }
        $pesan = config("pembayaran.briva_response.gagal.{$kode}")
            ?? $briva->responseMessage
            ?? 'Bank Error...';
        throw new GagalE($pesan, 400);
    }
}
