<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pacientes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class PacientesController
{
    public function index()
    {
        $pacientes = Pacientes::with('eps')->get();
        return response()->json($pacientes);
    }

    public function registrarPaciente(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'documento' => 'required|numeric|unique:pacientes,documento',
            'correo' => 'required|email|unique:pacientes,correo',
            'clave' => 'required|string|min:6',
            'celular' => 'required|string|max:15',
            'fecha_nacimiento' => 'required|date_format:Y-m-d',
            'ciudad' => 'required|string|max:100',
            'id_eps' => 'required|integer|exists:eps,id',
            'Rh' => 'required|string|max:3',
            'genero' => 'required|in:Masculino,Femenino',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $validatedData['clave'] = Hash::make($validatedData['clave']);

        try {
            $paciente = Pacientes::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Paciente registrado exitosamente.',
                'data' => $paciente
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error al registrar paciente: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno del servidor.'], 500);
        }
    }

    public function show(string $id)
    {
        $pacientes = Pacientes::with('eps')->find($id);
        if (!$pacientes) {
            return response()->json(['message' => 'Pacientes no encontrado'], 404);
        }
        return response()->json($pacientes);
    }

    public function update(Request $request, string $id)
    {
        $pacientes = Pacientes::find($id);
        if (!$pacientes) {
            return response()->json(['message' => 'Paciente no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:255',
            'apellido' => 'string|max:255',
            'documento' => 'integer',
            'correo' => 'email|max:255',
            'clave' => 'string|min:6|max:15',
            'celular' => 'integer|min:10',
            'fecha_nacimiento' => 'date',
            'ciudad' => 'string|max:255',
            'eps' => 'string|max:255',
            'Rh' => 'string',
            'genero' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();
        if (isset($validatedData['clave'])) {
            $validatedData['clave'] = Hash::make($validatedData['clave']);
        }

        $pacientes->update($validatedData);

        return response()->json($pacientes);
    }

    public function destroy(string $id)
    {
        $pacientes = Pacientes::find($id);

        if (!$pacientes) {
            return response()->json(['message' => "Paciente no encontrado"], 404);
        }

        $pacientes->delete();
        return response()->json(['message' => "Paciente eliminado correctamente"]);
    }

    public function listarHombres()
    {
        $mujeres = Pacientes::where('genero', 'Masculino')->get();
        return response()->json($mujeres);
    }

    public function listarCitasDePaciente($id)
    {
        $pacientes = Pacientes::find($id);

        if (!$pacientes) {
            return response()->json(['message' => 'Paciente no encontrado.'], 404);
        }

        $citas = $pacientes->citas;
        return response()->json($citas);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('eps');
        return response()->json([
            "success" => true,
            "user" => $user
        ]);
    }

    //Editar perfil
    public function updatePacientePerfil(Request $request)
    {
        $patientId = $request->user()->id;

        $pacientes = Pacientes::find($patientId);
        if (!$pacientes) {
            return response()->json(['message' => 'Paciente no encontrado o no autenticado'], 404);
        }
        // 🔹 Validación mejorada para la actualización del perfil
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:100',
            'apellido' => 'sometimes|string|max:100',
            'documento' => 'sometimes|numeric|unique:pacientes,documento,' . $patientId,
            'correo' => 'sometimes|email|max:255|unique:pacientes,correo,' . $patientId,
            'celular' => 'sometimes|string|max:15',
            'fecha_nacimiento' => 'sometimes|date_format:Y-m-d',
            'ciudad' => 'sometimes|string|max:100',
            'id_eps' => 'sometimes|integer|exists:eps,id', 
            'Rh' => 'sometimes|string|max:3',
            'genero' => 'sometimes|in:Masculino,Femenino',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        // No se debe permitir cambiar la clave desde el perfil
        if (isset($validatedData['clave'])) {
            unset($validatedData['clave']);
        }

        $pacientes->update($validatedData);

        // Devolver el paciente actualizado con su EPS
        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente.',
            'data' => $pacientes->load('eps')
        ]);
    }


    //Historial medico
    public function HistorialCitas(Request $request)
    {
        $paciente = $request->user();

        if (!$paciente) {
            return response()->json(['message' => 'Paciente no autenticado'], 401);
        }

        $today = now()->toDateString();

        $citasHistorial = $paciente->citas()
            ->with('doctor')
            ->whereDate('fecha', '<', $today)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();

        return response()->json($citasHistorial);
    }

    // Próximas citas pendientes
    public function ProximasCitasPendientes(Request $request)
    {
        $paciente = $request->user();

        if (!$paciente) {
            return response()->json(['message' => 'Paciente no autenticado'], 401);
        }

        $today = now()->toDateString();

        $proximasCitas = $paciente->citas()
            ->with('doctor')
            ->whereDate('fecha', '>=', $today)
            ->where('estado', 'pendiente')
            ->orderBy('fecha', 'asc')
            ->orderBy('hora', 'asc')
            ->get();

        return response()->json($proximasCitas);
    }


        // Próximas citas confirmadas
    public function ProximasCitasConfirmadas(Request $request)
    {
        $paciente = $request->user();

        if (!$paciente) {
            return response()->json(['message' => 'Paciente no autenticado'], 401);
        }

        $today = now()->toDateString();

        $proximasCitas = $paciente->citas()
            ->with('doctor')
            ->whereDate('fecha', '>=', $today)
            ->where('estado', 'confirmada')
            ->orderBy('fecha', 'asc')
            ->orderBy('hora', 'asc')
            ->get();

        return response()->json($proximasCitas);
    }


    //Cambiar clave
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ], [
                'new_password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
                'new_password.min' => 'La nueva contraseña debe tener al menos :min caracteres.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->clave)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña actual proporcionada es incorrecta.',
            ], 400);
        }

        $user->clave = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente.',
        ], 200);
    }


    //Eliminar cuenta /**

    public function deleteAccount(Request $request)
    {
        // Obtener el paciente autenticado a través del token
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Paciente no autenticado. Inicia sesión de nuevo.'
            ], 401);
        }

        try {
            // 1. ELIMINACIÓN DE REGISTROS DEPENDIENTES (CASCADA MANUAL)
            // Esto es necesario para evitar problemas de clave foránea.
            // Requiere que la función citas() esté en el modelo Pacientes.php
            $user->citas()->delete();

            // 2. ELIMINAR LA CUENTA
            // Usamos forceDelete() para asegurar la eliminación física, anular Soft Deletes si existen.
            $user->forceDelete();

            // 3. Opcional: Invalidar el token de sesión actual (si se usa jwt-auth)
            // Aunque el token se invalida al borrar el usuario, esto es una buena práctica:
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Tu cuenta ha sido eliminada permanentemente.'
            ], 200);
        } catch (\Exception $e) {
            // Usamos la notación global para evitar problemas de importación
            \Illuminate\Support\Facades\Log::error("Error al eliminar cuenta de paciente {$user->id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error de servidor: No se pudo completar la eliminación.'
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $query = $request->query('q');

        if (empty($query) || strlen($query) < 3) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $pacientes = Pacientes::where('nombre', 'LIKE', "%{$query}%")
            ->orWhere('apellido', 'LIKE', "%{$query}%")
            ->orWhere('documento', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pacientes
        ]);
    }


    //Buscar por cedula 
    public function buscarPorDocumento($documento)
    {
        $paciente = \App\Models\Pacientes::with('eps')->where('documento', $documento)->first();

        if (!$paciente) {
            return response()->json([
                'success' => false,
                'message' => 'Paciente no encontrado con ese documento.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $paciente
        ]);
    }

    //buscar paciente 
    public function buscar(Request $request){
        $term = $request->input('q'); // el parámetro que llega desde el front
        Log::info('🔍 Búsqueda recibida:', ['term' => $term]);
        if (!$term) {
            return response()->json(['success' => false, 'message' => 'Debe ingresar un término de búsqueda.'], 400);
        }

        $pacientes = \App\Models\Pacientes::with('eps')->where('documento', 'LIKE', "%$term%")
            ->orWhere('nombre', 'LIKE', "%$term%")
            ->orWhere('apellido', 'LIKE', "%$term%")
            ->limit(20) // para no traer toda la tabla
            ->get();

        return response()->json(['success' => true, 'data' => $pacientes], 200);
    }

        /**
     * Actualizar el estado de una cita (confirmar o cancelar)
     */
    public function CancelarCita($id, Request $request)
    {
        $request->validate([
            'estado' => 'required|in:,cancelada'
        ]);

        try {
            $cita = \App\Models\Citas::findOrFail($id);
            $cita->estado = $request->estado;
            $cita->save();

            return response()->json([
                'success' => true,
                'message' => "Cita {$request->estado} correctamente.",
                'data' => $cita
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Error actualizando estado de cita {$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la cita.'
            ], 500);
        }
    }


}
