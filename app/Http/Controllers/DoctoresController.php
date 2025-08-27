<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Doctores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class DoctoresController extends Controller
{
     public function index(){
        $doctores = Doctores::all();
        return response()->json($doctores);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'nombre'=> 'required|string|max:255',
            'apellido'=> 'required|string|max:255',
            'documento'=> 'required|integer',
            'correo'=> 'string|max:255',
            'clave'=> 'required|string|max:10',
            'celular'=> 'required|integer|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }

        $doctores = Doctores::create($validator->validate());

        return response()->json($doctores,201);
    }

    public function show(string $id){
        $doctores = Doctores::find($id);
        if (!$doctores) {
            return response()->json(['message'=> 'Doctor no encontrado'], 404);
        }
        return response()->json($doctores);
    }

    public function update(Request $request, string $id){
        $doctores = Doctores::find($id);
        if (!$doctores) {
            return response()->json(['message'=> 'Doctor no encontrado'], 404);
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

        $doctores->update($validator->validated());

        return response()->json($doctores);
    }

    public function destroy(string $id){
        $doctores = Doctores::find($id);
        if (!$doctores) {
            return response()->json(['message' => "Doctor no encontrado"], 404);
        }

        $doctores->delete();
        return response()->json(['message' => "Doctor eliminado correctamente"]);
    }
}
