<?php

namespace App\Http\Resources\Api\v1\Pangkalan;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KitirBrivaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $kadaluarsa = $this->kadaluarsa;


        return [
            'idBriva'       => $this->id_briva,
            // 'kitirId'       => $this->kitir_penjualan_ID,
            'jumlahBayar'   => $this->jumlah_bayar,
            'keterangan'    => $this->ket,
            'tanggalTf'     => $this->tanggal_tf,
            'statusBayar'   => $this->status_bayar,
            'kadaluarsa'    => $this->kadaluarsa,
            'waktuTransfer' => $this->waktu_transfer?Carbon::parse($this->waktu_transfer)->format('d-m-Y H:i:s'): null,
            // 'manualCek'     => $this->manual_cek,
            // 'userId'        => $this->id_u,
        ];
    }
}
