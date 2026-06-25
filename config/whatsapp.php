<?php

return [
    'driver'  => env('WHATSAPP_DRIVER', 'dovesoft'),    // dovesoft | log (test)
    'api_url' => env('DOVESOFT_API_URL', 'https://api.dovesoft.io/REST/directApi/message'),
    'api_key' => env('DOVESOFT_API_KEY', ''),
    'sender'  => env('DOVESOFT_SENDER', ''),
    'timeout' => 15,
];
