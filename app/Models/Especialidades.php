<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidades extends Model
{
    protected $fillable =  [
        'nombre',
    ];

    public function especialidades()
    {
        return $this->belongsToMany(Especialidades::class, 'especialidades_doctores', 'id_doctor', 'id_especialidad');
    }

    public function citas()
    {
        return $this->hasMany(Citas::class, 'id_doctor');
    }

    public function doctores()
    {
        return $this->belongsToMany(Doctores::class, 'especialidades_doctores', 'id_especialidad', 'id_doctor');
    }
}
