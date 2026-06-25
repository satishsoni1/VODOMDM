<?php

return [
    'server_url' => env('HEADWIND_MDM_URL', 'https://g-mdm.globalspace.in'),
    'login'      => env('HEADWIND_MDM_LOGIN', 'admin'),
    'password'   => env('HEADWIND_MDM_PASSWORD', ''),   // MD5 hash as required by Headwind
    'page_size'  => env('HEADWIND_MDM_PAGE_SIZE', 100),
];
