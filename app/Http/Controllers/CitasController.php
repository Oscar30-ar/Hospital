<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\Citas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CitasController extends Controller
{
   public function index() {
        $citas = Citas::all();
        return response()->json($citas);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            'fecha'=>'required|date',
            'hora'=> 'required',
            'descripcion'=>'required|string|max:255',
            'consultorio'=>'required|string|max:255',
            'estado'=>'required|in:pendiente,confirmada,cancelada',
            'id_doctor'=> 'required|integer',
            'id_paciente'=> 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }

        $citas = Citas::create($validator->validate());

        return response()->json($citas,201);
    }

    public function show(string $id) {
        $citas = Citas::find($id);
        if (!$citas) {
            return response()->json(['message'=> 'Cita no encontrada'], 404);
        }
        return response()->json($citas);
    
    }

    public function update(Request $request, string $id) {
        $citas = Citas::find($id);
        if (!$citas) {
            return response()->json(['message'=> 'Cita no encontrada'], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'fecha' => 'date',
            'hora' => 'string|max:255',
            'descripcion' => 'string|max:255',
            'consultorio' => 'string|max:255',
            'estado' => 'in:pendiente,confirmada,cancelada',
            'id_doctor' => 'integer',
            'id_paciente' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $citas->update($validator->validated());

        return response()->json($citas);
    }

    public function destroy(string $id){
        $citas = Citas::find($id);
        if (!$citas) {
             return response()->json(['message' => "Cita no encontrada"], 404);
        }

        $citas->delete();
        return response()->json(['message' => "Cita eliminada correctamente"]);
    }

    public function totalCitas(){
        $totalCitas = Citas::count();
        return response()->json(['totalCitas'=>$totalCitas]);
    }

    
}
