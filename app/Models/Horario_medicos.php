<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario_medicos extends Model
{
    protected $fillable = [
        'id_doctor',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctores::class, 'id_doctor');
    }
}
