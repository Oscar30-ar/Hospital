<?php

namespace App\Http\Controllers;

use App\Models\Horario_medicos;
use Illuminate\Http\Request;
use App\Models\HorarioMedico;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HorarioMedicosController extends Controller
{
    //listar horario del doctor autenticado
     public function listarHorarios()
    {
        $doctor = Auth::guard('doctor')->user();

        $horarios = Horario_medicos::where('id_doctor', $doctor->id)
            ->orderByRaw("FIELD(dia_semana, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo')")
            ->orderBy('hora_inicio', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $horarios
        ]);
    }
    //agregar horario 
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dia_semana' => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $doctor = Auth::guard('doctor')->user();

        // Evitar solapamiento de horarios
        $existe = Horario_medicos::where('id_doctor', $doctor->id)
            ->where('dia_semana', $request->dia_semana)
            ->where(function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('hora_inicio', '<', $request->hora_fin)
                       ->where('hora_fin', '>', $request->hora_inicio);
                });
            })
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes una franja que se solapa con ese horario.'
            ], 409);
        }

        $horario = Horario_medicos::create([
            'id_doctor' => $doctor->id,
            'dia_semana' => $request->dia_semana,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Horario agregado correctamente.',
            'data' => $horario
        ], 201);
    }


    //Editar horario
    public function update(Request $request, $id)
    {
        $request->validate([
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        $doctor = Auth::guard('doctor')->user();
        $horario = Horario_medicos::find($id);

        if (!$horario) {
            return response()->json(['success' => false, 'message' => 'Horario no encontrado.'], 404);
        }

        if ($horario->id_doctor !== $doctor->id) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $horario->update([
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Horario actualizado correctamente.',
            'data' => $horario
        ]);
    }

    // Eliminar horario

    public function destroy($id)
    {
        $doctor = Auth::guard('doctor')->user();
        $horario = Horario_medicos::find($id);

        if (!$horario) {
            return response()->json(['success' => false, 'message' => 'Horario no encontrado.'], 404);
        }

        if ($horario->id_doctor !== $doctor->id) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $horario->delete();

        return response()->json([
            'success' => true,
            'message' => 'Horario eliminado correctamente.'
        ]);
    }

}
