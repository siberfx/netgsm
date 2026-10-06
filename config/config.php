<?php

return [
    'credentials' => [
        'user_code' => env('NETGSM_USERCODE'),
        'secret' => env('NETGSM_SECRET'),
        'brand_code' => env('NETGSM_BRANDCODE'),
    ],
    'defaults' => [
        // Default sender name (msgheader) used when a message does not set one.
        'header' => env('NETGSM_HEADER'),
        // Set to "TR" to send messages containing Turkish characters; leave empty otherwise.
        'encoding' => env('NETGSM_ENCODING'),
        // Optional application name reported to NetGsm with each request.
        'appname' => env('NETGSM_APPNAME'),
        // Optional NetGsm partner code.
        'partner_code' => env('NETGSM_PARTNER_CODE'),
        'base_uri' => env('NETGSM_BASE_URI', 'https://api.netgsm.com.tr'),
        'timeout' => (int) env('NETGSM_TIMEOUT', 60),
    ],
];
