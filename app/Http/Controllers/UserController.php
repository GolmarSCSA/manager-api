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

            // Para localhost
            $cookie = cookie(name: 'refresh_token', value: $refreshToken, minutes: 60, path: null, domain: 'localhost', secure: false, httpOnly: true); // 60 minutes, HttpOnly

            // Para producción
            // $cookie = cookie('access_token', $accessToken, 60, '/', 'yourdomain.com', true, true); // 60 minutes, Secure, HttpOnly

            DB::commit();

            return response()->json([
                'message' => 'Usuario registrado correctamente',
                'user' => $userResponse,
                'access_token' => $accessToken,
            ], 201)->cookie($cookie);

        } catch (\Throwable $th) {
            DB::rollBack();
            return dd($th);
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
}
