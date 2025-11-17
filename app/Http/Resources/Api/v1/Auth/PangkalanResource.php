<?php

namespace App\Http\Resources\Api\v1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PangkalanResource extends JsonResource
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

            


        ];
    }
}
