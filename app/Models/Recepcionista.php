<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Recepcionista extends Authenticatable implements JWTSubject
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

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}