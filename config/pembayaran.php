<?php

return [


    'BRI_NOTIF_CLIENT_ID' => env('BRI_NOTIF_CLIENT_ID'),
    'BRI_NOTIF_CLIENT_SECRET' => env('BRI_NOTIF_CLIENT_SECRET'),
    'BRI_NOTIF_TOKEN_EXPIRES' => env('BRI_NOTIF_TOKEN_EXPIRES'),
    'BRI_NOTIF_public_key_bri' => storage_path(env('BRI_NOTIF_BRI_PUBLIC_KEY_PATH')),

    'base_url' => env('BRI_BASE_URL'),
    'client_id' => env('X_CLIENT_KEY'),
    // 'private_key_bri' => env('BRI_PRIVATE_KEY_PATH'),
    'private_key_bri' => storage_path(env('BRI_PRIVATE_KEY_PATH')),
    'client_secret' => env('CLIENT_SECRET'),
    'patner_id' => env('PATNER_ID'),
    'x_partner_id' => env('X_PARTNER_ID'),
    'url_notif_webhook' => env('URL_NOTIF_WEBHOOK'),

    'briva_response' => [
        'berhasil' => [
            '2002700' => 'Successful',
            '2002800' => 'Successful',
            '2002800' => 'Successful',
            '2002900' => 'Successful',
            '2003000' => 'Successful',
            '2003100' => 'Successful',
            '2003500' => 'Successful',
            '2002600' => 'Successful',
        ],
        'gagal' => [

            // A. Create VA

            '4002700' => 'Bad Request',
            '4002701' => 'Invalid Field Format',
            '4002702' => 'Invalid Mandatory Field',
            '4012700' => 'Unauthorized. Client Forbidden Access API',
            '4042712' => 'Invalid Bill/Virtual Account',
            '4042713' => 'Invalid Amount',
            '4042716' => 'Partner Not Found',
            '4092701' => 'Conflict',
            '5002700' => 'General Error',
            '5042700' => 'Timeout',

            // B. Update VA

            '4002800' => 'Bad Request',
            '4002801' => 'Invalid Field Format',
            '4002802' => 'Invalid Mandatory Field',
            '4012800' => 'Unauthorized. Client Forbidden Access API',
            '4042812' => 'Invalid Bill/Virtual Account',
            '4042813' => 'Invalid Amount',
            '4042816' => 'Partner Not Found',
            '4092801' => 'Conflict',
            '5002800' => 'General Error',
            '5042800' => 'Timeout',

            // C. Update Status VA

            '4002900' => 'Bad Request',
            '4002901' => 'Invalid Field Format',
            '4002902' => 'Invalid Mandatory Field',
            '4012900' => 'Unauthorized. Client Forbidden Access API',
            '4042912' => 'Invalid Bill/Virtual Account',
            '4042913' => 'Invalid Amount',
            '4042916' => 'Partner Not Found',
            '4092901' => 'Conflict',
            '5002900' => 'General Error',
            '5042900' => 'Timeout',

            // D. Inquiry VA

            '4003000' => 'Bad Request',
            '4003001' => 'Invalid Field Format',
            '4003002' => 'Invalid Mandatory Field',
            '4013000' => 'Unauthorized. Client Forbidden Access API',
            '4043012' => 'Invalid Bill/Virtual Account',
            '4043013' => 'Invalid Amount',
            '4043016' => 'Partner Not Found',
            '4093001' => 'Conflict',
            '5003000' => 'General Error',
            '5043000' => 'Timeout',

            // E. Delete VA

            '4003100' => 'Bad Request',
            '4003101' => 'Invalid Field Format',
            '4003102' => 'Invalid Mandatory Field',
            '4013100' => 'Unauthorized. Client Forbidden Access API',
            '4043112' => 'Invalid Bill/Virtual Account',
            '4043113' => 'Invalid Amount',
            '4043116' => 'Partner Not Found',
            '4093101' => 'Conflict',
            '5003100' => 'General Error',
            '5043100' => 'Timeout',

            // F. Get Report

            '4003500' => 'Bad Request',
            '4003501' => 'Invalid Field Format',
            '4003502' => 'Invalid Mandatory Field',
            '4013500' => 'Unauthorized. Client Forbidden Access API',
            '4043512' => 'Invalid Bill/Virtual Account',
            '4043513' => 'Invalid Amount',
            '4043516' => 'Partner Not Found',
            '4093501' => 'Conflict',
            '5003500' => 'General Error',
            '5043500' => 'Timeout',

            // G. Inquiry Status VA

            '4002600' => 'Bad Request',
            '4002601' => 'Invalid Field Format',
            '4002602' => 'Invalid Mandatory Field',
            '4012600' => 'Unauthorized. Client Forbidden Access API',
            '4042612' => 'Invalid Bill/Virtual Account',
            '4042613' => 'Invalid Amount',
            '4092601' => 'Conflict',
            '5002600' => 'General Error',
            '5042600' => 'Timeout',
        ],
    ]
];
