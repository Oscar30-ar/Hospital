<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Doctores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash; 

class DoctoresController extends Controller
{
     public function index(){
        $doctores = Doctores::all();
        return response()->json($doctores);
    }

    public function registrarDoctor(Request $request){
        $validator = Validator::make($request->all(),[
            'nombre'=> 'required|string|max:255',
            'apellido'=> 'required|string|max:255',
            'documento'=> 'required|integer|unique:doctores',
            'correo'=> 'string|max:255|unique:doctores',
            'clave'=> 'required|string|min:6|max:10',
            'celular'=> 'required|integer|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }

        $validatedData = $validator->validated();
        $validatedData['clave'] = Hash::make($validatedData['clave']); // Encriptar la contraseña

        $doctores = Doctores::create($validatedData);

        return response()->json($doctores,201);
    }

    // ... El resto de tu código para show, update y destroy
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
        
        $validatedData = $validator->validated();
        if (isset($validatedData['clave'])) {
            $validatedData['clave'] = Hash::make($validatedData['clave']);
        }

        $doctores->update($validatedData);

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

    public function buscarDoctorPorCedula($id)
    {
        $doctores = Doctores::where('documento', $id)->first();

        if (!$doctores) {
            return response()->json(['message' => 'Doctor no encontrado.'], 404);
        }

        return response()->json($doctores);
    }
}