<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pacientes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PacientesController
{
    public function index() {
    $pacientes = Pacientes::all();
    return response()->json($pacientes);
    }

    public function store(Request $request) {
    $validator = Validator::make($request->all(),[
        'nombre'=>'required|string|max:255',
        'apellido'=>'required|string|max:255',
        'documento'=>'required|integer',
        'correo'=>'required|email',
        'clave'=>'required|string|max:15',
        'celular'=>'required|integer|min:10',
        'fecha_nacimiento'=>'required|date',
        'ciudad'=>'required|string|max:255',
        'eps'=>'required|string|max:255',
        'Rh' => 'required',
        'genero' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(),422);
    }

    $pacientes = Pacientes::create($validator->validate());

    return response()->json($pacientes,201);
    }



    public function show(string $id) {
    $pacientes = Pacientes::find($id);
    if (!$pacientes) {
        return response()->json(['message'=> 'Pacientes no encontrado'], 404);
    }
    return response()->json($pacientes);
    }

    public function update(Request $request, string $id) {
    $pacientes = Pacientes::find($id);
    if (!$pacientes) {
        return response()->json(['message'=> 'Paciente no encontrado'], 404);
    }

    $validator = Validator::make($request->all(),[
        'nombre' => 'string|max:255',
        'apellido' => 'string|max:255',
        'documento' => 'integer',
        'correo' => 'email|max:255',
        'clave' => 'string|max:15',
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

    $pacientes->update($validator->validated());

    return response()->json($pacientes);
    }

    public function destroy(string $id){
        $pacientes = Pacientes::find($id);

        if (!$pacientes) {
            return response()->json(['message' => "Paciente no encontrado"], 404);
        }


        $pacientes->delete();
        return response()->json(['message' => "Paciente eliminado correctamente"]);
    }
} 