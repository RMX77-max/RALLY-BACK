<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie','storage/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['https://rally-front.netlify.app','http://localhost:9000', 'http://127.0.0.1:9000','https://rallycomarapa.netlify.app'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
