<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Citas;
use App\Models\Doctores;
use App\Models\Especialidades;
use App\Models\Pacientes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Validation\ValidationException;


class DoctoresController extends Controller
{
    public function index()
    {
        $doctores = Doctores::with('especialidades')->get(); // 👈 carga relación
        return response()->json([
            'success' => true,
            'data' => $doctores
        ]);
    }

    public function listardoc()
    {
        $citas = Doctores::all();
        return response()->json($citas);
    }

    public function registrarDoctor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'documento' => 'required|integer|unique:doctores',
            'correo' => 'string|max:255|unique:doctores',
            'clave' => 'required|string|min:6|max:10',
            'celular' => 'required|integer|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();
        $validatedData['clave'] = Hash::make($validatedData['clave']); // Encriptar la contraseña

        $doctores = Doctores::create($validatedData);

        return response()->json($doctores, 201);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'documento' => 'required|numeric|unique:doctores,documento',
            'correo' => 'required|email|unique:doctores,correo',
            'celular' => 'required|string|max:15',
            'clave' => 'required|string|min:6',
            'especialidades' => 'required|array',
            'especialidades.*' => 'exists:especialidades,id',
        ]);

        $doctor = Doctores::create([
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'documento' => $validated['documento'],
            'correo' => $validated['correo'],
            'celular' => $validated['celular'],
            'clave' => bcrypt($validated['clave']),
        ]);

        // 🔹 Asignar especialidades al doctor
        $doctor->especialidades()->sync($validated['especialidades']);

        return response()->json([
            'success' => true,
            'message' => 'Médico agregado correctamente',
            'data' => $doctor->load('especialidades'),
        ], 201);
    }




    // Obtener médico por ID
    public function listardoctores($id)
    {
        $doctor = Doctores::with('especialidades')->find($id);

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $doctor->only(['id', 'nombre', 'apellido', 'documento', 'correo', 'celular']),
            'especialidades' => $doctor->especialidades
        ]);
    }

    // Actualizar datos de médico
    public function editardoctores(Request $request, $id)
    {
        $doctor = Doctores::find($id);

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor no encontrado'
            ], 404);
        }

        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'documento' => 'required|string|max:20',
            'correo' => 'required|email|max:150',
            'celular' => 'required|string|max:15',
            'especialidades' => 'array|required'
        ]);

        $doctor->update($request->only(['nombre', 'apellido', 'documento', 'correo', 'celular']));

        // Actualizar especialidades (si es relación many-to-many)
        if ($request->has('especialidades')) {
            $doctor->especialidades()->sync($request->especialidades);
        }

        return response()->json([
            'success' => true,
            'message' => 'Médico actualizado correctamente',
            'data' => $doctor
        ]);
    }

    // Obtener todas las especialidades
    public function listarespecialidades()
    {
        $especialidades = Especialidades::all();
        return response()->json([
            'success' => true,
            'data' => $especialidades
        ]);
    }


















    // ... El resto de tu código para show, update y destroy
    public function show(string $id)
    {
        $doctores = Doctores::find($id);
        if (!$doctores) {
            return response()->json(['message' => 'Doctor no encontrado'], 404);
        }
        return response()->json($doctores);
    }

    public function update(Request $request, string $id)
    {
        $doctores = Doctores::find($id);
        if (!$doctores) {
            return response()->json(['message' => 'Doctor no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:255',
            'apellido' => 'string|max:255',
            'documento' => 'integer',
            'correo' => 'string|max:255',
            'clave' => 'string|max:10',
            'celular' => 'integer|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();
        if (isset($validatedData['clave'])) {
            $validatedData['clave'] = Hash::make($validatedData['clave']);
        }

        $doctores->update($validatedData);

        return response()->json($doctores);
    }

    public function destroy(string $id)
    {
        $doctores = Doctores::find($id);
        if (!$doctores) {
            return response()->json(['message' => "Doctor no encontrado"], 404);
        }

        $doctores->delete();
        return response()->json(['message' => "Doctor eliminado correctamente"]);
    }

    public function buscarDoctorPorCedula($id)
    {
        $doctores = Doctores::where('documento', $id)->first();

        if (!$doctores) {
            return response()->json(['message' => 'Doctor no encontrado.'], 404);
        }

        return response()->json($doctores);
    }

    public function me(Request $request)
    {
        return response()->json([
            "success" => true,
            "user" => $request->user()
        ]);
    }

    public function citasPendientes(Request $request)
    {
        $doctor = $request->user();

        $total = \App\Models\Citas::where('id_doctor', $doctor->id)
            ->where('estado', 'pendiente')
            ->count();

        return response()->json(['total' => $total]);
    }

    public function totalPacientes()
    {
        $total = \App\Models\Pacientes::count();
        return response()->json(['total' => $total]);
    }

    public function estadisticas(Request $request)
    {
        $doctor = $request->user();
        $today = Carbon::today()->toDateString(); // 👈 Siempre usa Carbon

        // Depuración
        \Illuminate\Support\Facades\Log::info("Calculando estadísticas del doctor", [
            'doctor_id' => $doctor->id,
            'today' => $today,
            'citas_en_bd' => Citas::where('id_doctor', $doctor->id)->pluck('fecha'),
        ]);

        $citasHoy = Citas::where('id_doctor', $doctor->id)
            ->whereRaw('DATE(fecha) = ?', [$today]) // 👈 MÁS SEGURO que whereDate()
            ->count();

        $pacientesTotales = Citas::where('id_doctor', $doctor->id)
            ->distinct('id_paciente')
            ->count('id_paciente');

        $pendientes = Citas::where('id_doctor', $doctor->id)
            ->where('estado', 'pendiente')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'citasHoy' => $citasHoy,
                'pacientesTotales' => $pacientesTotales,
                'pendientes' => $pendientes,
            ]
        ]);
        try {
            $doctor = $request->user();

            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor no autenticado.'
                ], 401);
            }

            // Fecha actual (solo día, sin hora)
            $today = now()->toDateString();

            // 🔹 Citas programadas para hoy
            $citasHoy = \App\Models\Citas::where('id_doctor', $doctor->id)
                ->whereDate('fecha', $today)
                ->count();

            // 🔹 Total de pacientes únicos atendidos o con cita
            $pacientesTotales = \App\Models\Citas::where('id_doctor', $doctor->id)
                ->distinct('id_paciente')
                ->count('id_paciente');

            // 🔹 Citas pendientes del doctor
            $pendientes = \App\Models\Citas::where('id_doctor', $doctor->id)
                ->where('estado', 'pendiente')
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'citasHoy' => $citasHoy,
                    'pacientesTotales' => $pacientesTotales,
                    'pendientes' => $pendientes,
                ]
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error obteniendo estadísticas del doctor {$request->user()->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas del médico.'
            ], 500);
        }
    }


    //citas hoy 
    public function citasHoy(Request $request)
    {
        $doctor = $request->user();

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor no autenticado.'
            ], 401);
        }

        $today = Carbon::today()->toDateString();

        $citasHoy = Citas::with(['pacientes:id,nombre,apellido', 'doctor:id,nombre'])
            ->where('id_doctor', $doctor->id)
            ->whereRaw('DATE(fecha) = ?', [$today])
            ->orderBy('hora', 'asc')
            ->get(['id', 'fecha', 'hora', 'estado', 'consultorio', 'id_paciente', 'id_doctor']);

        return response()->json([
            'success' => true,
            'data' => $citasHoy
        ]);
    }

    //Mis pacientes 
    public function misPacientes(Request $request)
    {
        $doctor = $request->user();

        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor no autenticado.'], 401);
        }

        try {
            // Usamos la relación 'pacientes' que definimos en el modelo Doctores.
            // El método distinct() es importante para no obtener pacientes duplicados
            // si un paciente tiene múltiples citas con el mismo doctor.
            $pacientes = $doctor->pacientes()
                ->distinct()
                ->orderBy('pacientes.apellido', 'asc') // Especificamos la tabla para el orderBy
                ->get([
                    'pacientes.id', // Es buena práctica especificar la tabla para evitar ambigüedades
                    'pacientes.nombre',
                    'pacientes.apellido',
                    'pacientes.documento',
                    'pacientes.celular',
                    'pacientes.correo'
                ]);

            // Respuesta con los datos (será una lista vacía si no hay coincidencias)
            return response()->json([
                'success' => true,
                'data' => $pacientes,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error en misPacientes (whereHas) para doctor {$doctor->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al obtener la lista de pacientes.',
            ], 500);
        }
    }

    //historial de paciente 

    public function historialPaciente(Request $request, $pacienteId)
    {
        try {
            // Con JWTAuth:
            $doctor = JWTAuth::parseToken()->authenticate();

            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor no autenticado.'
                ], 401);
            }
            $citas = \App\Models\Citas::with([
                'doctor:id,nombre,apellido',
                'pacientes:id,nombre,apellido,documento'
            ])
                ->where('id_doctor', $doctor->id)
                ->where('id_paciente', $pacienteId)
                ->orderBy('fecha', 'desc')
                ->get([
                    'id',
                    'fecha',
                    'hora',
                    'descripcion',
                    'consultorio',
                    'estado',
                    'id_doctor',
                    'id_paciente'
                ]);

            return response()->json([
                'success' => true,
                'data' => $citas
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error historial paciente {$pacienteId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial del paciente.'
            ], 500);
        }
    }

    //Editar perfil
    public function updateMedicoPerfil(Request $request)
    {
        $doctorId = $request->user()->id;

        $pacientes = Doctores::find($doctorId);
        if (!$pacientes) {
            return response()->json(['message' => 'Paciente no encontrado o no autenticado'], 404);
        }
        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:255',
            'apellido' => 'string|max:255',
            'documento' => 'integer|unique:pacientes,documento,' . $doctorId,
            'correo' => 'email|max:255|unique:pacientes,correo,' . $doctorId,
            'celular' => 'integer|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();

        if (isset($validatedData['clave'])) {
            unset($validatedData['clave']);
        }

        $pacientes->update($validatedData);

        return response()->json($pacientes);
    }

    /**
     * Elimina la cuenta del doctor autenticado y sus datos asociados.
     */
    public function deleteAccount(Request $request)
    {
        // Obtener el doctor autenticado a través del token
        $doctor = $request->user();

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor no autenticado. Inicia sesión de nuevo.'
            ], 401);
        }

        try {
            // 1. ELIMINACIÓN DE REGISTROS DEPENDIENTES (CASCADA MANUAL)
            // Esto es necesario para evitar problemas de clave foránea.
            $doctor->citas()->delete();
            $doctor->especialidades()->delete();

            // 2. ELIMINAR LA CUENTA DEL DOCTOR
            // Usamos forceDelete() para asegurar la eliminación física.
            $doctor->forceDelete();

            // 3. Invalidar el token de sesión actual para cerrar la sesión.
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Tu cuenta de doctor ha sido eliminada permanentemente.'
            ], 200);
        } catch (\Exception $e) {
            // Registrar el error para depuración
            Log::error("Error al eliminar cuenta de doctor {$doctor->id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error de servidor: No se pudo completar la eliminación de la cuenta.'
            ], 500);
        }
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

    public function doctoresPorEspecialidad($id)
    {
        $doctores = Doctores::whereHas('especialidades', function ($query) use ($id) {
            $query->where('especialidades.id', $id);
        })->select('id', 'nombre', 'apellido')->get();

        if ($doctores->isEmpty()) {
            return response()->json([]); // Devolver array vacío si no hay resultados
        }

        return response()->json($doctores);
    }
}
