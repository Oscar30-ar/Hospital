<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctores extends Model
{
    protected $fillable = [
        'nombre',
        'apellido',
        'documento',
        'correo',
        'clave',
        'celular',
    ];

    public function especialides(){
        return $this->hasMany(Especialides::class,'id_doctor');
    }

    public function citas(){
        return $this->hasMany(Citas::class, 'id_doctor');
    }
}
