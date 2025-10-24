<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Citas; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Pacientes extends Authenticatable implements JWTSubject
{
     use HasApiTokens, HasFactory, Notifiable; 
    protected $fillable = [
        'nombre',
        'apellido',
        'documento',
        'correo',
        'clave',
        'celular',
        'fecha_nacimiento',
        'ciudad',
        'id_eps',
        'Rh',
        'genero',
    ];

    public function citas()
    {
        return $this->hasMany(\App\Models\Citas::class, 'id_paciente');
    }


    public function eps()
    {
        return $this->belongsTo(Eps::class, 'id_eps');
    }

public function doctores()
    {
        return $this->belongsToMany(Doctores::class, 'citas', 'id_paciente', 'id_doctor')
            ->withTimestamps();
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