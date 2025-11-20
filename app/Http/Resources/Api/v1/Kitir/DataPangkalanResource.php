<?php

namespace App\Http\Resources\Api\v1\Kitir;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataPangkalanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_pang' => $this->id_pang,
            'nama' => $this->nama,
            'id_sim' => $this->id_sim,
            'no_briva' => config('app.briva').$this->no_briva,
            'email' => $this->email,
            'pin' => $this->pin,
        ];
    }
}
