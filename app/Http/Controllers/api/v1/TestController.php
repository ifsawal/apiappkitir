<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\UserPenyalur;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function tes(){
        // return UserPenyalur::all();
        return encrypt("12345678");
    }
}
