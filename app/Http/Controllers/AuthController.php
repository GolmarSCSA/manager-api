<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Método para registrar un nuevo usuario
    public function register(RegisterRequest $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Usuario registrado correctamente'], 201);
    }

    // Método para iniciar sesión y obtener un token
    public function login(Request $request)
    {
        dd($request);

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

    
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Las credenciales no son correctas'], 401);
        }

        // Crear el token de acceso
        $token = $user->createToken('access_token')->plainTextToken;

        return response()->json(['token' => $token], 200);
    }

    // Método para cerrar sesión y revocar el token
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente'], 200);
    }
}
