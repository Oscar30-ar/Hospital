<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recepcionista extends Model
{
    protected $fillable =  [
        'nombre',
        'apellido',
        'documento',
        'correo',
        'clave',
        'celular',
    ];

    public function citas(){
        return $this->hasMany(Citas::class, 'id_recepcionista');
    }
}
