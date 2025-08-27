<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Especialidades;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EspecialidadesController
{
    public function index() {
    $especialidades = Especialidades::all();
    return response()->json($especialidades);
    }

    public function store(Request $request) {
    $validator = Validator::make($request->all(),[
        'nombre'=> 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(),422);
    }

    $especialidades = Especialidades::create($validator->validate());

    return response()->json($especialidades,201);
    }

    public function show(string $id) {
        $especialidades = Especialidades::find($id);

        if (!$especialidades) {
            return response()->json(['message'=> 'Especialidad no encontrada'], 404);
        }

        return response()->json($especialidades);
    }

    public function update(Request $request, string $id) {
        $especialidades = Especialidades::find($id);

        if (!$especialidades) {
            return response()->json(['message'=> 'Especialidad no encontrada'], 404);
        }

        $validator = Validator::make($request->all(),[
            'nombre'=>'string|max:250',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $especialidades->update($validator->validated());

        return response()->json($especialidades);
    }

    public function destroy(string $id){
        $especialidades = Especialidades::find($id);
        if (!$especialidades) {
            return response()->json(['message' => "Especialid no encontrada"], 404);
        }

        $especialidades->delete();
        return response()->json(['message' => "Especialid eliminada correctamente"]);
    }
}
