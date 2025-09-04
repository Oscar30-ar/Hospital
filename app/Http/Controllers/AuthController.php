<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Doctores;
use App\Models\Pacientes;
use App\Models\Recepcionista;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('correo', 'clave');

        // Intenta autenticar a un Doctor, Paciente o Recepcionista.
        $user = Doctores::where('correo', $credentials['correo'])->first();
        if (!$user) {
            $user = Pacientes::where('correo', $credentials['correo'])->first();
        }
        if (!$user) {
            $user = Recepcionista::where('correo', $credentials['correo'])->first();
        }

        if (!$user || !Hash::check($credentials['clave'], $user->clave)) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('token'));
    }

    public function logout()
    {
        $user = auth('api')->user();

        if ($user) {
            auth('api')->logout();

            return response()->json(['message' => $user->nombre . ' ha cerrado sesión correctamente']);
        }

        return response()->json(['message' => 'No se pudo cerrar la sesión'], 401);
    }
}
