<?php

if (!function_exists('create_cookie')) {
    function create_cookie($refreshToken) {
        return cookie(
            name: 'refresh_token',
            value: $refreshToken,
            minutes: 60 * 24 * 7,    // Duración: 7 días
            path: '/',               // Disponible en toda la aplicación
            domain: null, // Laravel lo configura automáticamente
            secure: false,            // Requiere HTTPS
            httpOnly: true,          // Protege contra acceso por JavaScript
            sameSite: 'Lax'         // Necesario para cross-origin
        );
        
    }
    
}
