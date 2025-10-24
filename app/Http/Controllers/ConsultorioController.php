<?php

namespace App\Http\Controllers;

use App\Models\Doctores;
use App\Models\Consultorio; // <-- CAMBIO: Sigue la convención de nombres de Laravel (PascalCase)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ConsultorioController extends Controller
{
    //listar consultorios
    public function listarConsultorios()
    {
        $consultorios = Consultorio::all();
        return response()->json([
            'success' => true,
            'data' => $consultorios
        ]);
    }

    /**
     * Lista solo los consultorios que no están asignados a ningún doctor.
     */
    public function listarConsultoriosDisponibles()
    {
        try {
            // 1. Obtener los IDs de los consultorios que ya están en uso por los doctores.
            $assignedConsultorioIds = Doctores::whereNotNull('id_consultorio')->pluck('id_consultorio');

            // 2. Obtener los consultorios cuyo ID no está en la lista de asignados.
            $availableConsultorios = Consultorio::whereNotIn('id', $assignedConsultorioIds)->get();

            return response()->json([
                'success' => true,
                'data' => $availableConsultorios
            ]);
        } catch (\Exception $e) {
            Log::error("Error al listar consultorios disponibles: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno del servidor.'], 500);
        }
    }

    //crear consultorio
    public function store(Request $request)
    {
        // Validación más robusta para evitar duplicados
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:consultorios,nombre', // El nombre del consultorio debe ser único
        ], [
            'nombre.unique' => 'Ya existe un consultorio con este nombre.',
        ]);
 
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
 
        try {
            $consultorio = Consultorio::create($validator->validated());
 
            // Respuesta JSON estandarizada y más descriptiva
            return response()->json([
                'success' => true,
                'message' => 'Consultorio creado exitosamente.',
                'data' => $consultorio
            ], 201);
 
        } catch (\Exception $e) {
            Log::error("Error al crear el consultorio: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno del servidor.'], 500);
        }
    }

    //Editar consultorio
    public function update(Request $request, string $id){
         $consultorio = Consultorio::find($id);

        if (!$consultorio) {
            return response()->json(['message' => 'Consultorio no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:250',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $consultorio->update($validator->validated());

        return response()->json($consultorio);
    }

    //obtener un consultorio por id
    public function consultorioByID($id){
        $consultorio = Consultorio::find($id);
        if (!$consultorio) {
            return response()->json(['message' => 'Consultorio no encontrado'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $consultorio->only(['id', 'nombre'])
        ]);
    }

    //Eliminar consultorio
        public function destroy(string $id)
    {
        $consultorio = Consultorio::find($id);
        if (!$consultorio) {
            return response()->json(['message' => "Consultorio no encontrado"], 404);
        }

        $consultorio->delete();
        return response()->json(['message' => "Consultorio eliminado correctamente"]);
    }
}
