<?php

namespace App\Http\Resources\Api\v1\Kitir;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KitirResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        
        return [
            'id_k' => $this->id_k,
            'id_pang' => $this->id_pang,
            'tanggal_kitir' => $this->tanggal,
            'jumlah' => $this->jumlah,
            'ket' => $this->ket,
            'tgl_masuk' => $this->tgl_masuk,
            'nama_pang' => $this->nama,
            'user' => $this->user,
            'kitir_pecah'=>$this->kitir_pecah,
            


        ];
    }
}
