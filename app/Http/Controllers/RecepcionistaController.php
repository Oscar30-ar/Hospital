<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Citas;
use App\Models\Recepcionista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

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

    public function me(Request $request)
    {
        return response()->json([
            "success" => true,
            "user" => $request->user()
        ]);
    }


    
    //Editar perfil
    public function updateRecepcionPerfil(Request $request)
    {
        $recepcionId = $request->user()->id;

        $recepcion = Recepcionista::find($recepcionId);
        if (!$recepcion) {
            return response()->json(['message' => 'Recepcionista no encontrado o no autenticado'], 404);
        }
        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:255',
            'apellido' => 'string|max:255',
            'documento' => 'integer|unique:pacientes,documento,' . $recepcionId,
            'correo' => 'email|max:255|unique:pacientes,correo,' . $recepcionId,
            'celular' => 'integer|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();

        if (isset($validatedData['clave'])) {
            unset($validatedData['clave']);
        }

        $recepcion->update($validatedData);

        return response()->json($recepcion);
    }

/**
 * Elimina la cuenta del recepcionista autenticado y sus datos asociados.
 */
public function deleteAccount(Request $request)
{
    // Obtener el recepcionista autenticado mediante JWT
    $recepcionista = $request->user();

    if (!$recepcionista) {
        return response()->json([
            'success' => false,
            'message' => 'Recepcionista no autenticado. Inicia sesión de nuevo.'
        ], 401);
    }

    try {
        // 🔹 1. Eliminar citas asociadas (si las tiene)
        // Asumiendo que la relación está definida como:
        // public function citas() { return $this->hasMany(Citas::class, 'id_recepcionista'); }
        if (method_exists($recepcionista, 'citas')) {
            $recepcionista->citas()->delete();
        }

        // 🔹 2. Eliminar al recepcionista de forma definitiva
        $recepcionista->forceDelete();

        // 🔹 3. Invalidar el token JWT para cerrar sesión
        if ($token = JWTAuth::getToken()) {
            JWTAuth::invalidate($token);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tu cuenta de recepcionista ha sido eliminada permanentemente.'
        ], 200);

    } catch (\Exception $e) {
        Log::error("❌ Error al eliminar cuenta del recepcionista {$recepcionista->id}: " . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Error de servidor: no se pudo completar la eliminación de la cuenta.',
            'error' => $e->getMessage()
        ], 500);
    }
}


    //Cambiar clave
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ], [
                'new_password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
                'new_password.min' => 'La nueva contraseña debe tener al menos :min caracteres.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->clave)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña actual proporcionada es incorrecta.',
            ], 400);
        }

        $user->clave = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente.',
        ], 200);
    }


}