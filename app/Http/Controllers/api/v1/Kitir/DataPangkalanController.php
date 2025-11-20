<?php

namespace App\Http\Controllers\api\v1\Kitir;

use App\Models\Pangkalan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\v1\Kitir\DataPangkalanResource;
use Dflydev\DotAccessData\Data;

class DataPangkalanController extends Controller
{
    public function getPangkalan($cari = null)
    {

        if ($cari) {
            // Jika ada parameter pencarian
            $pangkalan = Pangkalan::where('nama', 'like', "%$cari%")->get();
        } else {
            // Jika tidak ada parameter pencarian
            $pangkalan = Pangkalan::all();
        }


        return response()->json([
            'status' => true,
            'data' => DataPangkalanResource::collection($pangkalan),

        ], 202);
    }
}
