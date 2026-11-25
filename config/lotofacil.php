<?php
// config/lotofacil.php

return [
    'window' => env('LOTOFACIL_WINDOW', 100),
    'hot_cold_window' => env('LOTOFACIL_HOT_COLD_WINDOW', 50),
    'thermal_window' => env('LOTOFACIL_THERMAL_WINDOW', 30),
    'candidatos_base' => env('LOTOFACIL_CANDIDATOS', 5000),

    'auth' => [
        'user' => env('LOTOFACIL_USER', 'admin'),
        'password' => env('LOTOFACIL_PASSWORD', 'admin123'),
    ],
];
