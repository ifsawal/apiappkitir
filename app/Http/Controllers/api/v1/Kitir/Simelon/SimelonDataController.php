<?php

namespace App\Http\Controllers\api\v1\Kitir\Simelon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SimelonDataController extends Controller
{
    public function simelon_data()
    {
        return response()->json([
            'status' => true,
            'data' => [
                'link' => 'https://apps.pertamina.com/simelon_v2/home/index',
                'link-log' => 'https://apps.pertamina.com/simelon_v2/login',
                'link-das' => 'https://apps.pertamina.com/simelon_v2/dashboard',
                'user' => '888278',
                'pass' => '2021@Pertamina2985',
            ],
        ], 202);
    }
}
