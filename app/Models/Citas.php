<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Doctores;
class Citas extends Model
{
    protected $fillable = [
        'fecha',
        'hora',
        'descripcion',
        'id_doctor',
        'id_recepcionista',
        'id_paciente',
        'estado',
        'consultorio',
    ];

    public function pacientes()
    {
        return $this->belongsTo(Pacientes::class, 'id_paciente');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctores::class, 'id_doctor');
    }
    
}
