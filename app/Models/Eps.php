<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eps extends Model
{
    protected $fillable = [
        'nombre',
    ];

    public function pacientes()
    {
        return $this->hasMany(Pacientes::class, 'id_eps');
    }
}
