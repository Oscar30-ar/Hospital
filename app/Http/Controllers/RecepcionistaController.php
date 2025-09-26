<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Recepcionista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class RecepcionistaController
{
    public function index() {
        $recepcionista = Recepcionista::all();
        return response()->json($recepcionista);
    }

    public function registrarRecepcionista(Request $request) {
        $validator = Validator::make($request->all(),[
            'nombre'=>'required|string|max:255',
            'apellido'=> 'required|string|max:255',
            'documento'=> 'required|integer|unique:recepcionistas',
            'correo'=> 'required|email|unique:recepcionistas',
            'clave'=> 'required|string|min:6|max:10',
            'celular'=> 'required|integer|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }
        
        $validatedData = $validator->validated();
        $validatedData['clave'] = Hash::make($validatedData['clave']); 

        $recepcionista = Recepcionista::create($validatedData);

        return response()->json($recepcionista,201);
    }

    public function show(string $id) {
    $recepcionista = Recepcionista::find($id);
        if (!$recepcionista) {
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
            'clave' => 'string|min:6|max:10',
            'celular' => 'integer|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $validatedData = $validator->validated();
        if (isset($validatedData['clave'])) {
            $validatedData['clave'] = Hash::make($validatedData['clave']);
        }

        $recepcionista->update($validatedData);

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