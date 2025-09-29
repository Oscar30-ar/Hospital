<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Doctores;
use App\Models\Pacientes;
use App\Models\Recepcionista;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        $token = auth($role)->login($user);

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
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
                'success' => true,
                'message' => $user->nombre . ' ha cerrado sesión correctamente'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo cerrar la sesión'
        ], 401);
    }



    public function mePaciente()
    {
        return response()->json(auth('paciente')->user());
    }

    public function meDoctor()
    {
        return response()->json(auth('doctor')->user());
    }

    public function meRecepcionista()
    {
        return response()->json(auth('recepcionista')->user());
    }

    private function findUserAndTable(string $email): ?array
    {
        // 1. Check Doctores
        $user = Doctores::where('correo', $email)->first();
        if ($user) {
            return ['user' => $user, 'tableName' => 'doctores'];
        }

        // 2. Check Pacientes
        $user = Pacientes::where('correo', $email)->first();
        if ($user) {
            return ['user' => $user, 'tableName' => 'pacientes'];
        }

        // 3. Check Recepcionista
        $user = Recepcionista::where('correo', $email)->first();
        if ($user) {
            return ['user' => $user, 'tableName' => 'recepcionistas'];
        }

        return null;
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'correo' => 'required|email', // Se elimina la validación 'exists' específica
        ]);

        // Usamos la función auxiliar para verificar la existencia del usuario en cualquier tabla.
        $userInfo = $this->findUserAndTable($request->correo);

        if (!$userInfo) {
            // Se envía un mensaje genérico por seguridad, aunque el 404 también es aceptable.
            return response()->json([
                'success' => false,
                'message' => 'No se encontró un usuario con ese correo electrónico.',
            ], 404);
        }

        // Si el usuario existe, el resto del proceso es el mismo, solo necesitamos el correo.
        $token = Str::random(60);

        // Guardar token cifrado en la tabla password_resets
        // Esto es universal ya que usa el correo como llave, independientemente del rol.
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->correo],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Enviar correo con el token (o con un enlace en producción)
        Mail::to($request->correo)->send(new ResetPasswordMail($token));

        Log::info("🔐 Token de restablecimiento enviado a {$request->correo}: $token");

        return response()->json([
            'success' => true,
            'message' => 'Se ha enviado un enlace de restablecimiento de contraseña a tu correo electrónico.',
        ], 200);
    }


    /**
     * Restablece la contraseña del usuario.
     * Busca el correo en cualquiera de las tres tablas para actualizar el campo 'clave'.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'correo' => 'required|email', // Se elimina la validación 'exists' específica
            'token' => 'required|string',
            'clave' => 'required|string|min:6|max:10|confirmed', // usa clave_confirmation
        ]);

        // 1. Identificar al usuario y la tabla
        $userInfo = $this->findUserAndTable($request->correo);

        if (!$userInfo) {
            return response()->json([
                'success' => false,
                'message' => 'El correo electrónico no corresponde a ningún usuario.',
            ], 404);
        }

        $tableName = $userInfo['tableName'];

        // 2. Buscar el registro del token
        $reset = DB::table('password_resets')
            ->where('email', $request->correo)
            ->first();

        if (!$reset) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró una solicitud de restablecimiento para este correo.',
            ], 404);
        }

        // 3. Validar el token cifrado
        if (!Hash::check($request->token, $reset->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido o expirado.',
            ], 400);
        }

        // 4. Actualizar la contraseña del usuario en la tabla correcta
        DB::table($tableName) // ¡Uso dinámico de la tabla!
            ->where('correo', $request->correo)
            ->update([
                'clave' => Hash::make($request->clave),
                'updated_at' => now(),
            ]);

        // 5. Borrar el token usado
        DB::table('password_resets')->where('email', $request->correo)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tu contraseña ha sido restablecida correctamente.',
        ], 200);
    }
}
