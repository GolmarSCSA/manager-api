<?php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Incluye 'sanctum/csrf-cookie'
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000'], // Dominio del frontend
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // Necesario para cookies
];
