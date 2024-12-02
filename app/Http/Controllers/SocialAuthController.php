<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            // Obtén los datos del usuario desde Google
            $googleUser = Socialite::driver('google')->user();
    
            // Busca al usuario en la base de datos por correo electrónico
            $user = User::where('email', $googleUser->getEmail())->first();
    
            if ($user) {
                // Si el usuario ya existe, inicia sesión
                Auth::login($user);
    
                // Genera un Access Token
                $tokenResult = $user->createToken('access_token');
                $token = $tokenResult->accessToken;
    
                // Obtener el rol y país del usuario
                $role = $user->roles->first(); // Obtiene el primer rol asignado al usuario (Spatie permite múltiples roles)
                $country = $user->country;    // Asume una relación belongsTo con Country
    
                // Construir la respuesta del usuario
                $userResponse = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'surname' => $user->surname,
                    'email' => $user->email,
                    'company' => $user->company,
                    'nif' => $user->nif,
                    'address' => $user->address,
                    'city' => $user->city,
                    'zip_code' => $user->zip_code,
                    'phone' => $user->phone,
                    'prefix_id' => $user->prefix_id,
                    'code_prefix' => $country->codeISO2 ?? null, // Manejo de nulos
                    'role_id' => $role->id ?? null,              // Manejo de nulos
                    'role' => $role->name ?? null,               // Nombre del rol (manejo de nulos)
                    'country_id' => $user->country_id,
                    'country' => __('countries.' . ($country->language_field ?? '')), // Manejo de nulos
                    'email_verified_at' => $user->email_verified_at,
                ];
    
                return response()->json([
                    'message' => 'Inicio de sesión exitoso',
                    'user' => $userResponse,
                    'token' => $token,
                ], 200);
            }
    
            // Si el usuario no existe, devuelve los datos de Google para registro posterior
            return response()->json([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ], 404);
    
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'error' => 'Error al autenticar con Google',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function redirectToLinkedIn()
    {
        return Socialite::driver('linkedin-openid')->redirect();
    }

    public function handleLinkedInCallback()
    {
        try {
            // Obtén los datos del usuario desde LinkedIn
            $linkedinUser = Socialite::driver('linkedin-openid')->user();
    
            // Extrae los datos básicos del usuario de LinkedIn
            $name = $linkedinUser->getName();
            $email = $linkedinUser->getEmail();
            $linkedinId = $linkedinUser->getId();
            $avatar = $linkedinUser->getAvatar();
    
            // Busca al usuario en la base de datos por correo electrónico
            $user = User::where('email', $email)->first();
    
            if ($user) {
                // Si el usuario ya existe, inicia sesión
                Auth::login($user);
    
                // Genera un Access Token
                $tokenResult = $user->createToken('access_token');
                $token = $tokenResult->accessToken;
    
                // Obtener el rol y país del usuario
                $role = $user->roles->first(); // Obtiene el primer rol asignado al usuario
                $country = $user->country;    // Relación belongsTo con Country
    
                // Construir la respuesta del usuario
                $userResponse = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'surname' => $user->surname,
                    'email' => $user->email,
                    'company' => $user->company,
                    'nif' => $user->nif,
                    'address' => $user->address,
                    'city' => $user->city,
                    'zip_code' => $user->zip_code,
                    'phone' => $user->phone,
                    'prefix_id' => $user->prefix_id,
                    'code_prefix' => $country->codeISO2 ?? null, // Manejo de nulos
                    'role_id' => $role->id ?? null,              // Manejo de nulos
                    'role' => $role->name ?? null,               // Nombre del rol (manejo de nulos)
                    'country_id' => $user->country_id,
                    'country' => __('countries.' . ($country->language_field ?? '')), // Manejo de nulos
                    'email_verified_at' => $user->email_verified_at,
                ];
    
                return response()->json([
                    'message' => 'Inicio de sesión exitoso',
                    'user' => $userResponse,
                    'token' => $token,
                ], 200);
            }
    
            // Si el usuario no existe, devuelve los datos de LinkedIn para el flujo de registro
            return response()->json([
                'name' => $name,
                'email' => $email,
                'linkedin_id' => $linkedinId, // Identificador único de LinkedIn
                'avatar' => $avatar,          // Imagen de perfil
            ], 404);
    
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'error' => 'Error al autenticar con LinkedIn',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
