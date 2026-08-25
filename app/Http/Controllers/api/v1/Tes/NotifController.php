<?php

namespace App\Http\Controllers\api\v1\Tes;

use App\Http\Controllers\Controller;
use App\Services\NotifServices;
use Illuminate\Http\Request;

class NotifController extends Controller
{
    public function tes_notif(Request $request, NotifServices $n)
    {
        // return $n->ambilTokenFCM();
        $tokentujuan="e9C9vlH_S0u3GEWEYLw-fa:APA91bEXLtE7kYBLeBKKE8nb526TN5vnt4jSlGRr6gsXt8K0N8IFJfGtu6q50r3Mam4itEu4Kc5YxLcM59yODsbKfn6vqzfQwjf5LVoxKoucDLg4IDNEXoU";
        return $n->kirimFCM($tokentujuan, "Hai", "Hai ini ada body");
    }
}
