<?php

namespace App\Http\Controllers;

use App\Models\Eps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class EpsController extends Controller
{
    //listar Eps
    public function listarEps()
    {
        $eps = Eps::all();
        return response()->json([
            'success' => true,
            'data' => $eps
        ]);
    }

    //Crear una nueva eps
    public function CrearEps(Request $request)
    {
        // Validación para asegurar que el nombre sea único
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:eps,nombre',
        ], [
            'nombre.unique' => 'Ya existe una EPS con este nombre.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $eps = Eps::create($validator->validated());

            // Respuesta JSON estandarizada
            return response()->json([
                'success' => true,
                'message' => 'EPS creada exitosamente.',
                'data' => $eps
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error al crear la EPS: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno del servidor.'], 500);
        }
    }

    //obtener una eps por id
    public function EpsByID($id)
    {
        $eps = Eps::find($id);
        if (!$eps) {
            return response()->json(['message' => 'Eps no encontrada'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $eps->only(['id', 'nombre'])
        ]);
    }

    //Editar Eps
    public function update(Request $request, string $id)
    {
        $eps = Eps::find($id);

        if (!$eps) {
            return response()->json(['message' => 'Eps no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:250',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $eps->update($validator->validated());

        return response()->json($eps);
    }

    //Eliminar eps
    public function destroy(string $id)
    {
        $eps = Eps::find($id);
        if (!$eps) {
            return response()->json(['message' => "Eps no encontrada"], 404);
        }

        $eps->delete();
        return response()->json(['message' => "Eps eliminada correctamente"]);
    }
}
