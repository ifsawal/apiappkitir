<?php

namespace App\Http\Resources\Api\v1\Pangkalan;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransaksiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'tanggal'       => $this->tanggal ? Carbon::parse($this->tanggal)->format('d-m-Y'): null,
            'jumlah'        => $this->jumlah,
            'status'        => $this->status,
            'harga'         => $this->harga,
            'notif'         => $this->notif,
            'sisaBayar'     => $this->sis_bayar,

            // relasi → gunakan resource terpisah
            'briva'         => new KitirBrivaResource($this->whenLoaded('kitir_penjualan_briva')),
        ];
    }
}
