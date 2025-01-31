<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\FirstStepRequest;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Token;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserController extends Controller
{
    public function firstStepWizard(Request $request)
    {
        
        try {
            DB::beginTransaction();

            /** @var User */
            $user = Auth::user();  

            // Actualizar los campos del usuario
            $this->updateUser($user, $request);

            // Asignar el rol al usuario
            $role = Role::find($request->role_id);
            $user->assignRole($role->name);

            // Generar la respuesta del usuario
            $userResponse = $this->generateUserResponse($user, $role);

            // Enviar correo de verificación
            Mail::to($user->email)->send(new \App\Mail\VerificationCodeMail($user->verification_code));

            // Simular inicio de sesión
            $tokenResult = $user->createToken('access_token');
            $accessToken = $tokenResult->accessToken;
            $refreshToken = $tokenResult->token->id; 

            $cookie = create_cookie($refreshToken);
            
            DB::commit();

            return response()->json([
                'message' => 'Usuario registrado correctamente',
                'user' => $userResponse,
                'access_token' => $accessToken,
            ], 201)->cookie($cookie);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], 500);
        }

    }

    private function updateUser($user, $request)
    {
        $user->company = $request->company;
        $user->nif = $request->nif;
        $user->address = $request->address;
        $user->city = $request->city;
        $user->zip_code = $request->zip_code;
        $user->phone = $request->phone;
        $user->prefix_id = $request->prefix_id;
        $user->country_id = $request->country_id;
        $user->save();
    }

    private function generateUserResponse($user, $role)
    {
        $country = $user->country;

        return [
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
            'code_prefix' => $country->codeISO2 ?? null,
            'role_id' => $role->id,
            'role' => $role->name,
            'country_id' => $user->country_id,
            'country' => isset($country) ? __('countries.' . $country->language_field) : null,
            'email_verified_at' => $user->email_verified_at,
        ];
    }

    public function changeLanguage(Request $request)
    {

        $verify = $request->validate([
            'language' => 'required|string|in:es,en,fr,nl,pt,de,el,ro,bg,hr,pv,cat,ga,sk,sr,da,cs,it',
        ]);

        /** @var User */
        $user = Auth::user();
        $user->language = $request->language;
        $user->save();

        return response()->json([
            'status' => 'success',
        ], 201);
    }

    public function createPasswordForgotCode(Request $request)
    {
        $verify = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'success',
            ], 200);
        }

        $user->password_forgot_code = Str::random(6);
        $user->password_forgot_expires_at = now()->addHours(24);
        $user->save();

        Mail::to($user->email)->send(new \App\Mail\VerificationCodeMail($user->password_forgot_code));

        return response()->json([
            'status' => 'success',
        ], 200);

    }

    public function validatePasswordForgotCode(Request $request)
    {
        $verify = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->where('password_forgot_code', $request->code)
            ->where('password_forgot_expires_at', '>=', now())
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Código de verificación incorrecto o expirado',
            ], 400);
        }

        $tokenResult = $user->createToken('password_reset_token');
        $passwordResetToken = $tokenResult->accessToken;

        return response()->json([
            'status' => 'success',
            'password_reset_token' => $passwordResetToken,
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $verify = $request->validate([
            'email' => 'required|email',
            'password_forgot_code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $token = $request->password_forgot_code;

        $user = $this->getUserFromAccessToken($token);

        if ($user->email == $request->email) {  
            $user->password = Hash::make($request->password);
            $user->password_forgot_code = null;
            $user->password_forgot_expires_at = null;
            $user->save();
        }

        return response()->json([
            'status' => 'success',
        ], 200);
    }

    public function getUserFromAccessToken($accessToken)
    {
        try {
            // Ruta de la clave pública generada por Laravel Passport
            $publicKey = file_get_contents(storage_path('oauth-public.key'));

            // Decodificar el token con la clave pública
            $decodedToken = JWT::decode($accessToken, new Key($publicKey, 'RS256'));

            // El campo 'sub' contiene el ID del usuario
            $userId = $decodedToken->sub;

            // Buscar el usuario por su ID
            $user = User::find($userId);

            if (!$user) {
                return null;
            }

            return $user;

        } catch (\Exception $e) {
            return response()->json(['message' => 'Token inválido o error al procesar: ' . $e->getMessage()], 400);
        }
    }
}
