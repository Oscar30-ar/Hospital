<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Recepcionista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecepcionistaController
{
    public function index() {
        $recepcionista = Recepcionista::all();
        return response()->json($recepcionista);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            'nombre'=>'required|string|max:255',
            'apellido'=> 'required|string|max:255',
            'documento'=> 'required|integer',
            'correo'=> 'string|max:255',
            'clave'=> 'required|string|max:10',
            'celular'=> 'required|integer|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }

        $recepcionista = Recepcionista::create($validator->validate());

        return response()->json($recepcionista,201);
    }

    public function show(string $id) {
    $recepcionista = Recepcionista::find($id);
        if (!$doctores) {
            return response()->json(['message'=> 'recepcionista no encontrado'], 404);
        }
        return response()->json($recepcionista);
    }

    public function update(Request $request, string $id) {
    $recepcionista = Recepcionista::find($id);
        if (!$recepcionista) {
            return response()->json(['message'=> 'recepcionista no encontrado'], 404);
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

        $recepcionista->update($validator->validated());

        return response()->json($recepcionista);
    }

    public function destroy(string $id){
        $recepcionista = Recepcionista::find($id);
        if (!$recepcionista) {
            return response()->json(['message' => "recepcionista no encontrado"], 404);
        }

        $recepcionista->delete();
        return response()->json(['message' => "recepcionista eliminado correctamente"]);
    }
}
