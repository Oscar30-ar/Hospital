<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pacientes extends Model
{
    protected $fillable = [
        'nombre',
        'apellido',
        'documento',
        'correo',
        'clave',
        'celular',
        'fecha_nacimiento',
        'ciudad',
        'eps',
        'Rh',
        'genero',
    ];

    public function citas(){
        return $this->hasMany(Citas::class, 'id_paciente');
    }
}
