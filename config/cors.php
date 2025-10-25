<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],

    'allowed_methods' => ['*'],

    // 👇 Lista completa de orígenes permitidos
    'allowed_origins' => [
        'https://rallycomarapa.netlify.app', // nuevo front (Netlify)
        'https://rally2025.netlify.app',     // antiguo (por si hay caché)
        'https://rally-front.netlify.app',   // antiguo (por si hay caché)
        'http://localhost:8080',             // quasar serve / dev
        'http://127.0.0.1:8080',
        'http://localhost:5173',             // vite, si lo usas
        'http://127.0.0.1:5173',
        'http://localhost:9000',             // si lo usas
        'http://127.0.0.1:9000',
        'http://localhost:9200',             // si lo usas
        'http://127.0.0.1:9200',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // ⬇️ Si usas cookies/sesión (Sanctum), debe ser true
    'supports_credentials' => true,

];
