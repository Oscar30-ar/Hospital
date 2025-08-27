<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidades extends Model
{
    protected $fillable =  [
        'nombre',
    ];

    public function especialidades(){
        return $this->belongsToMany(Especialidades::class, 'id_doctor');
    }
}
