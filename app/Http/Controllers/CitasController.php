<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\Citas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CitasController extends Controller
{
    public function index()
    {
        $citas = Citas::all();
        return response()->json($citas);
    }

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


    public function show(string $id)
    {
        $citas = Citas::find($id);
        if (!$citas) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }
        return response()->json($citas);
    }

    public function update(Request $request, string $id)
    {
        $citas = Citas::find($id);
        if (!$citas) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'fecha' => 'date',
            'hora' => 'string|max:255',
            'descripcion' => 'string|max:255',
            'consultorio' => 'string|max:255',
            'estado' => 'in:pendiente,confirmada,cancelada',
            'id_doctor' => 'integer',
            'id_paciente' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $citas->update($validator->validated());

        return response()->json($citas);
    }

    public function destroy(string $id)
    {
        $citas = Citas::find($id);
        if (!$citas) {
            return response()->json(['message' => "Cita no encontrada"], 404);
        }

        $citas->delete();
        return response()->json(['message' => "Cita eliminada correctamente"]);
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

    $citas = Citas::with(['pacientes:id,nombre,apellido,celular', 'doctor:id,nombre,apellido'])
        ->whereRaw('DATE(fecha) = ?', [$today])
        ->orderBy('hora', 'asc')
        ->get([
            'id',
            'fecha',
            'hora',
            'estado',
            'consultorio',
            'id_paciente',
            'id_doctor'
        ]);

    return response()->json([
        'success' => true,
        'data' => $citas
    ]);
}

//cambiar estado de cita 
 public function actualizarEstado($id, Request $request)
    {
        $estado = $request->input('estado');

        if (!$estado) {
            return response()->json([
                'success' => false,
                'message' => 'El campo estado es obligatorio.'
            ], 400);
        }

        $cita = Citas::find($id);

        if (!$cita) {
            return response()->json([
                'success' => false,
                'message' => 'Cita no encontrada.'
            ], 404);
        }

        $cita->estado = $estado;
        $cita->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado de la cita actualizado correctamente.',
            'data' => $cita
        ], 200);
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


}
