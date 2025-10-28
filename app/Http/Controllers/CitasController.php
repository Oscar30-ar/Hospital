<?php

namespace App\Http\Controllers;

use App\Models\Doctores;
use App\Models\Horario_medicos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Citas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Helpers\FirebaseHelper;

class CitasController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'id_paciente' => 'required|exists:pacientes,id',
            'id_doctor' => 'required|exists:doctores,id',
            'fecha' => 'required|date_format:Y-m-d',
            'hora' => 'required|date_format:H:i',
            'estado' => 'required|in:pendiente,confirmada,cancelada',
            'consultorio' => 'nullable|string',
            'descripcion' => 'nullable|string',
        ]);

        $recepcionista = auth('recepcionista')->user();
        if (!$recepcionista) {
            return response()->json(['message' => 'Acceso denegado. Solo Recepcionistas pueden crear citas.'], 403);
        }

        try {
            $cita = Citas::create([
                'id_paciente' => $request->id_paciente,
                'id_doctor' => $request->id_doctor,
                'id_recepcionista' => $recepcionista->id, // AÑADIDO: Captura el ID del recepcionista
                'fecha' => $request->fecha,
                'hora' => $request->hora,
                'estado' => $request->estado,
                'consultorio' => $request->consultorio ?? 'N/A',
                'descripcion' => $request->descripcion ?? 'Cita agendada por Recepcionista',
            ]);

            return response()->json(['success' => true, 'message' => 'Cita creada exitosamente.', 'data' => $cita], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear la cita.', 'error' => $e->getMessage()], 500);
        }
    }

    public function totalCitas()
    {
        $totalCitas = Citas::count();
        return response()->json(['totalCitas' => $totalCitas]);
    }

    public function availableTimes($id_doctor)
    {
        // Lógica de simulación de horarios (ejemplo simple)
        $availableSlots = [];
        $today = now();
        $consultorio = 'C303'; // Consultorio por defecto

        // Generar 7 días de horarios
        for ($i = 0; $i < 7; $i++) {
            $date = $today->copy()->addDays($i)->format('Y-m-d');

            // Simular franjas de 9:00 a 17:00 cada hora
            for ($h = 9; $h <= 17; $h++) {
                $time = sprintf('%02d:00', $h);
                $availableSlots[] = ['fecha' => $date, 'hora' => $time, 'consultorio' => $consultorio];
            }
        }

        // Excluir citas ya agendadas y confirmadas para ese doctor
        $bookedCitas = Citas::where('id_doctor', $id_doctor)
            ->where('fecha', '>=', $today->format('Y-m-d'))
            ->whereIn('estado', ['confirmada', 'pendiente'])
            ->get(['fecha', 'hora']);

        $bookedTimes = $bookedCitas->map(fn($cita) => $cita->fecha . ' ' . $cita->hora)->toArray();

        $filteredSlots = array_filter($availableSlots, function ($slot) use ($bookedTimes) {
            return !in_array($slot['fecha'] . ' ' . $slot['hora'], $bookedTimes);
        });

        // Asegurar que devuelve solo los valores sin claves numéricas
        return response()->json(array_values($filteredSlots));
    }
    public function estadisticasRecepcion()
    {
        try {
            $today = Carbon::today()->format('Y-m-d'); // "2025-09-28"

            $citasHoy = Citas::whereDate('fecha', $today)->count();
            $enEspera = Citas::where('estado', 'pendiente')->count();
            $confirmadas = Citas::where('estado', 'confirmada')->count();
            $canceladas = Citas::where('estado', 'cancelada')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'citasHoy' => $citasHoy,
                    'enEspera' => $enEspera,
                    'confirmadas' => $confirmadas,
                    'canceladas' => $canceladas,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    //citas hoy 
    public function citasHoyRecepcion()
    {
        $today = Carbon::now('America/Bogota')->format('Y-m-d');

        $citas = Citas::with(['paciente:id,nombre,apellido,celular,documento,correo,Rh,genero', 'doctor:id,nombre,apellido'])
            ->whereRaw('DATE(fecha) = ?', [$today])
            ->orderBy('hora', 'asc')
            ->get([
                'id',
                'fecha',
                'hora',
                'estado',
                'id_paciente',
                'id_doctor'
            ]);

        return response()->json([
            'success' => true,
            'data' => $citas
        ]);
    }

    public function citasHoy()
    {
        try {
            $hoy = Carbon::now()->toDateString();

            $citas = Citas::with(['pacientes', 'doctor'])
                ->whereDate('fecha', $hoy)
                ->orderBy('hora', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $citas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las citas de hoy: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todas las citas pendientes
     */
    public function citasPendientes()
    {
        try {
            $citas = \App\Models\Citas::with(['paciente', 'doctor'])
                ->where('estado', 'pendiente')
                ->orderBy('fecha', 'asc')
                ->orderBy('hora', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $citas
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Error al obtener citas pendientes: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las citas pendientes'
            ], 500);
        }
    }


    /**
     * Listar todos los doctores
     */
    public function listarDoctores()
    {
        return response()->json([
            'success' => true,
            'data' => Doctores::all(),
        ]);
    }

    /**
     * 🔹 Crear una cita como paciente autenticado
     */
    public function storePaciente(Request $request)
    {
        $request->validate([
            'id_doctor' => 'required|exists:doctores,id',
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $paciente = Auth::guard('paciente')->user();

        // 📆 🔸 Verificar si el paciente ya agendó una cita hoy (independiente de para qué fecha sea)
        $hoy = \Carbon\Carbon::now('America/Bogota')->toDateString();

        $yaCreoCitaHoy = \App\Models\Citas::where('id_paciente', $paciente->id)
            ->whereDate('created_at', $hoy) // se compara por fecha de creación, no fecha de cita
            ->exists();

        if ($yaCreoCitaHoy) {
            return response()->json([
                'success' => false,
                'message' => 'Ya has agendado una cita hoy. Solo puedes crear una cita por día.'
            ], 400);
        }


        // ✅ Verificar disponibilidad del doctor
        $verificacion = $this->verificarDisponibilidad($request->id_doctor, $request->fecha, $request->hora);
        if (!$verificacion['disponible']) {
            return response()->json([
                'success' => false,
                'message' => $verificacion['mensaje'] ?? 'El doctor no está disponible en ese horario.',
            ], 400);
        }

        // ✅ Crear la cita
        $cita = Citas::create([
            'id_paciente' => $paciente->id,
            'id_doctor' => $request->id_doctor,
            'fecha' => $request->fecha,
            'hora' => $request->hora,
            'descripcion' => $request->descripcion ?? 'Cita solicitada por el paciente.',
            'estado' => 'pendiente',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cita solicitada correctamente. Un recepcionista la confirmará pronto.',
            'data' => $cita,
        ], 201);
    }

    /**
     * 🔹 Reprogramar una cita por parte del paciente
     */
    public function reprogramarCita(Request $request, $id)
    {
        // ✅ Corregir formato de hora ANTES del validator
        if ($request->hora && strlen($request->hora) === 5) {
            $request->merge(['hora' => $request->hora . ':00']);
        }

        // ✅ Validar una sola vez con formato correcto
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date_format:Y-m-d|after_or_equal:today',
            'hora' => 'required|date_format:H:i:s',
        ], [
            'fecha.after_or_equal' => 'La fecha no puede ser en el pasado.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $paciente = Auth::guard('paciente')->user();
            $cita = Citas::findOrFail($id);

            if ($cita->id_paciente !== $paciente->id) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para modificar esta cita.'], 403);
            }

            if ($cita->estado === 'cancelada' || Carbon::parse($cita->fecha . ' ' . $cita->hora)->isPast()) {
                return response()->json(['success' => false, 'message' => 'Esta cita no se puede reprogramar.'], 400);
            }

            // ✅ Verificar disponibilidad del doctor
            $verificacion = $this->verificarDisponibilidad($cita->id_doctor, $request->fecha, $request->hora);
            if (!$verificacion['disponible']) {
                return response()->json([
                    'success' => false,
                    'message' => $verificacion['mensaje'],
                ], 409);
            }

            // ✅ Actualizar cita
            $cita->fecha = $request->fecha;
            $cita->hora = $request->hora;
            $cita->estado = 'pendiente';
            $cita->save();

            return response()->json([
                'success' => true,
                'message' => 'Cita reprogramada exitosamente. Queda pendiente de confirmación.',
                'data' => $cita->load('doctor', 'paciente'),
            ], 200);
        } catch (\Exception $e) {
            Log::error("❌ Error al reprogramar cita {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno al intentar reprogramar la cita.'], 500);
        }
    }


    /**
     * 🔹 Verificar disponibilidad API pública
     */
    public function disponibilidad($doctorId, Request $request)
    {
        $fecha = $request->query('fecha');
        $hora = $request->query('hora');

        $resultado = $this->verificarDisponibilidad($doctorId, $fecha, $hora);

        return response()->json([
            'success' => true,
            'disponible' => $resultado['disponible'],
            'mensaje' => $resultado['mensaje'],
        ]);
    }


    /**
     * 🔹 Lógica de verificación de disponibilidad del doctor
     */
    private function verificarDisponibilidad($doctorId, $fecha, $hora)
    {
        try {
            if (!$fecha || !$hora) {
                return [
                    'disponible' => false,
                    'mensaje' => 'Fecha u hora inválida.',
                ];
            }

            Carbon::setLocale('es');
            setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'es', 'es_CO.UTF-8');

            $fechaCarbon = Carbon::parse($fecha);
            $horaFormateada = strlen($hora) === 5 ? $hora . ':00' : $hora;
            $horaCarbon = Carbon::createFromFormat('H:i:s', $horaFormateada);

            // Mapeo de días
            $mapDias = [
                'monday' => 'Lunes',
                'tuesday' => 'Martes',
                'wednesday' => 'Miercoles',
                'thursday' => 'Jueves',
                'friday' => 'Viernes',
                'saturday' => 'Sábado',
                'sunday' => 'Domingo',
            ];

            $diaSemanaIngles = strtolower($fechaCarbon->format('l'));
            $diaSemana = $mapDias[$diaSemanaIngles] ?? ucfirst($diaSemanaIngles);

            // Buscar horarios del doctor ese día
            $horarios = Horario_medicos::where('id_doctor', $doctorId)
                ->where('dia_semana', $diaSemana)
                ->get();

            if ($horarios->isEmpty()) {
                return [
                    'disponible' => false,
                    'mensaje' => "El doctor no atiende los {$diaSemana}.",
                ];
            }

            // Validar si la hora cae dentro de una franja
            foreach ($horarios as $horario) {
                $inicio = Carbon::createFromFormat('H:i:s', $horario->hora_inicio);
                $fin = Carbon::createFromFormat('H:i:s', $horario->hora_fin);

                if ($horaCarbon->between($inicio, $fin)) {
                    return [
                        'disponible' => true,
                        'mensaje' => "El doctor está disponible el {$diaSemana} a las {$horaCarbon->format('H:i')}.",
                    ];
                }
            }

            return [
                'disponible' => false,
                'mensaje' => "El doctor no está disponible el {$diaSemana} a las {$horaCarbon->format('H:i')}.",
            ];
        } catch (\Throwable $e) {
            Log::error("❌ Error verificando disponibilidad: " . $e->getMessage());
            return [
                'disponible' => false,
                'mensaje' => 'Error interno al verificar disponibilidad.',
            ];
        }
    }

    public function actualizarEstadoCita(Request $request, $id)
    {
        $cita = Citas::find($id);

        if (!$cita) {
            return response()->json(['success' => false, 'message' => 'Cita no encontrada.'], 404);
        }

        $nuevoEstado = $request->estado;
        $cita->estado = $nuevoEstado;
        $cita->save();

        // ✅ Enviar notificación al paciente
        $paciente = $cita->paciente;

        if ($paciente && $paciente->expo_token) {
            $titulo = "Actualización de tu cita médica";
            $mensaje = match ($nuevoEstado) {
                'confirmada' => "Tu cita fue CONFIRMADA ✅. Te esperamos el {$cita->fecha} a las {$cita->hora}.",
                'cancelada' => "Tu cita fue CANCELADA ❌. Por favor agenda una nueva si lo deseas.",
                default => "Tu cita cambió de estado a: {$nuevoEstado}.",
            };

            FirebaseHelper::enviarNotificacion($paciente->expo_token, $titulo, $mensaje);
        }


        return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente.']);
    }

    private function enviarNotificacionExpo($token, $titulo, $mensaje)
    {
        try {
            $response = Http::post('https://exp.host/--/api/v2/push/send', [
                'to' => $token,
                'sound' => 'default',
                'title' => $titulo,
                'body' => $mensaje,
            ]);

            Log::info('📲 Notificación enviada', [
                'token' => $token,
                'response' => $response->json(),
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('❌ Error al enviar notificación: ' . $e->getMessage());
            return false;
        }
    }

    public function verificarCambioEstado()
    {
        // Buscar citas confirmadas hoy (puedes ajustar la condición)
        $citas = Citas::with('paciente')
            ->where('estado', 'confirmada')
            ->whereDate('updated_at', now()->toDateString())
            ->get();

        foreach ($citas as $cita) {
            if ($cita->paciente && $cita->paciente->expo_token) {
                $this->enviarNotificacionExpo(
                    $cita->paciente->expo_token,
                    '✅ Cita Confirmada',
                    'Tu cita del ' . $cita->fecha . ' ha sido confirmada.'
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Notificaciones enviadas a pacientes con citas confirmadas.',
        ]);
    }
}
