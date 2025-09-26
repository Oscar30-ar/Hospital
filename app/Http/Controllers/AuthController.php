<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Doctores;
use App\Models\Pacientes;
use App\Models\Recepcionista;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('correo', 'clave', 'role');
        $role = strtolower($credentials['role']);

        $user = null;

        switch ($role) {
            case 'doctor':
                $user = Doctores::where('correo', $credentials['correo'])->first();
                break;
            case 'paciente':
                $user = Pacientes::where('correo', $credentials['correo'])->first();
                break;
            case 'recepcionista':
                $user = Recepcionista::where('correo', $credentials['correo'])->first();
                break;
        }

        if (!$user || !Hash::check($credentials['clave'], $user->clave)) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $token = auth($role)->login($user);

        return response()->json([
            'token' => $token,
            'role' => $role,
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'correo' => $user->correo,
            ]
        ]);
    }


    public function logout(Request $request)
    {
        $role = $request->get('role');
        $user = auth($role)->user();

        if ($user) {
            auth($role)->logout();
            return response()->json([
                'message' => $user->nombre . ' ha cerrado sesión correctamente'
            ]);
        }

        return response()->json(['message' => 'No se pudo cerrar la sesión'], 401);
    }

 public function me(Request $request)
{
    if (auth('paciente')->check()) {
        return response()->json([
            'role' => 'paciente',
            'user' => auth('paciente')->user()
        ]);
    }

    if (auth('doctor')->check()) {
        return response()->json([
            'role' => 'doctor',
            'user' => auth('doctor')->user()
        ]);
    }

    if (auth('recepcionista')->check()) {
        return response()->json([
            'role' => 'recepcionista',
            'user' => auth('recepcionista')->user()
        ]);
    }

    return response()->json(['error' => 'No autenticado'], 401);
}

}
