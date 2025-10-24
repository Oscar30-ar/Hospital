<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidades_doctores extends Model
{
    protected $fillable = [
        'id_doctor',
        'id_especialidad',
    ];

    public function especialidad_doctor(){
        return $this->belongsTo(Especialidades_doctores::class,'id_especialidad');
    }

    public function doctor(){
        return $this->belongsTo(Doctores::class,'id_doctor');
    }
}
