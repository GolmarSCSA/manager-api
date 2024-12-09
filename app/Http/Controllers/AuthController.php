<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\RegisterRequest;
use Laravel\Passport\RefreshTokenRepository;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AuthController extends Controller
{
    // Método para registrar un nuevo usuario
    public function register(RegisterRequest $request)
    {

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'password' => Hash::make($request->password), 
                'company' => $request->company, 
                'nif' => $request->nif, 
                'address' => $request->address,
                'city' => $request->city, 
                'zip_code' => $request->zip_code,
                'phone' => $request->phone,
                'prefix_id' => $request->prefix_id,
                'role_id' => $request->role_id, 
                'terms_conditions' => $request->terms_conditions,
                'privacy_policy' => $request->privacy_policy,
                'country_id' => $request->country_id, 
                'verification_code' => Str::random(6), // Genera un código aleatorio de 6 caracteres
                'verification_expires_at' => now()->addMinutes(15), // Expira en 15 minutos
            ]);

            $role = Role::find($request->role_id);

            $user->assignRole($role->name);

            $country = $user->country;
            
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
                'code_prefix' => $country->codeISO2,
                'role_id' => $role->id,
                'role' => $role->name,
                'country_id' => $user->country_id,
                'country' => __('countries.' . $country->language_field),
                'email_verified_at' => $user->email_verified_at,
            ];


             // Enviar correo de verificación
            Mail::to($user->email)->send(new \App\Mail\VerificationCodeMail($user->verification_code));

            // Simular inicio de sesión
            $tokenResult = $user->createToken('access_token');
            $accessToken = $tokenResult->accessToken;
            $refreshToken = $tokenResult->token->id; // Assuming you are using Passport or Sanctum

            DB::commit();

            // Para localhost
            $cookie = cookie(name: 'refresh_token', value: $refreshToken, minutes: 60, path: null, domain: 'localhost', secure: false, httpOnly: true); // 60 minutes, HttpOnly

            // Para producción
            // $cookie = cookie('access_token', $accessToken, 60, '/', 'yourdomain.com', true, true); // 60 minutes, Secure, HttpOnly

            return response()->json([
                'message' => 'Usuario registrado correctamente',
                'user' => $userResponse,
                'access_token' => $accessToken,
            ], 201)->cookie($cookie);

        } catch (\Throwable $th) {
            
            DB::rollBack();

            return response()->json(['message' => $th->getMessage()], 500);
            //throw $th;
        }

        
    }

    // Método para iniciar sesión y obtener un token
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);


        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }
    
        $user = Auth::user();
        $tokenResult = $user->createToken('access_token');
        $accessToken = $tokenResult->accessToken;
        $refreshToken = $tokenResult->token->id; // Assuming you are using Passport or Sanctum
    
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

        // Para localhost
        $cookie = cookie('refresh_token', $refreshToken, 60, null, null, false, true); // 60 minutes, HttpOnly

        // Para producción
        // $cookie = cookie('access_token', $accessToken, 60, '/', 'yourdomain.com', true, true); // 60 minutes, Secure, HttpOnly

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'user' => $userResponse,
            'access_token' => $accessToken,
        ], 200)->cookie($cookie);
    }

    // Método para cerrar sesión y revocar el token
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente'], 200);
    }

    /**
     * Reenviar el correo de verificación.
     */
    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email sent.']);
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        $request->fulfill();
    
        // Agregar parámetros a la URL
        $redirectUrl = 'https://tu-app-react.com/email-verified?status=success';
    
        return redirect($redirectUrl);
    }
    

     /**
     * Comprobar si el email está verificado.
     */
    public function checkVerificationStatus(Request $request)
    {
        $user = $request->user();

        return response()->json(['verified' => $user->hasVerifiedEmail()]);
    }

    public function resendCode(Request $request)
    {
        // Validar el correo electrónico
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        // Buscar al usuario por correo
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Generar un nuevo código de verificación
        $user->verification_code = Str::random(6); // Genera un código aleatorio de 6 caracteres
        $user->verification_expires_at = now()->addMinutes(15); // Establece una expiración de 15 minutos
        $user->save();

        // Enviar el código por correo electrónico
        Mail::to($user->email)->send(new \App\Mail\VerificationCodeMail($user->verification_code));

        return response()->json(['message' => 'Verification code resent.'], 200);
    }

    public function verifyCode(Request $request)
    {
        // Validar los datos ingresados
        $validated = $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string',
        ]);

        // Buscar al usuario por correo
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Verificar si el código ingresado coincide y no ha expirado
        if ($user->verification_code !== $validated['verification_code']) {
            return response()->json(['message' => 'Invalid verification code.'], 400);
        }

        if (now()->greaterThan($user->verification_expires_at)) {
            return response()->json(['message' => 'Verification code has expired.'], 400);
        }

        // Marcar el email como verificado
        $user->email_verified_at = now();
        $user->verification_code = null; // Limpia el código de verificación
        $user->verification_expires_at = null; // Limpia la fecha de expiración
        $user->save();

        return response()->json(['message' => 'Email successfully verified.'], 200);
    }

    public function refreshToken(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        try {
            $refreshToken = $request->input('refresh_token');

            $refreshTokenRepository = app(RefreshTokenRepository::class);
            $refreshTokenModel = $refreshTokenRepository->find($refreshToken);

            if (!$refreshTokenModel || $refreshTokenModel->revoked) {
                return response()->json(['message' => 'Invalid refresh token'], 401);
            }

            $user = $refreshTokenModel->accessToken->user;

            $tokenResult = $user->createToken('access_token');
            $accessToken = $tokenResult->accessToken;
            $refreshToken = $tokenResult->token->id; 

            // Para localhost
            $cookie = cookie('refresh_token', $refreshToken, 60, null, null, false, true); // 60 minutes, HttpOnly

            // Para producción
            // $cookie = cookie('access_token', $accessToken, 60, '/', 'yourdomain.com', true, true); // 60 minutes, Secure, HttpOnly

            return response()->json([
                'message' => 'Token refreshed successfully',
                'access_token' => $accessToken,
            ])->cookie($cookie);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not refresh token'], 500);
        }
    }


}
