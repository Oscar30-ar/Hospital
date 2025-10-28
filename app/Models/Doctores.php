<?php

namespace App\Models;

use App\Http\Controllers\HorarioMedicosController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Doctores extends Authenticatable implements JWTSubject
{
    protected $fillable = [
        'nombre',
        'apellido',
        'documento',
        'correo',
        'clave',
        'celular',
        'id_consultorio',
    ];

    public function especialidades()
    {
        return $this->belongsToMany(Especialidades::class, 'especialidades_doctores', 'id_doctor', 'id_especialidad');
    }


    public function citas()
    {
        return $this->hasMany(Citas::class, 'id_doctor');
    }
    public function pacientes()
    {
        return $this->belongsToMany(Pacientes::class, 'citas', 'id_doctor', 'id_paciente')
            ->withTimestamps()->distinct();;
    }

    
    public function consultorio(){
        return $this->belongsTo(Consultorio::class, 'id_consultorio');
    }

    

    public function horarios()
    {
        return $this->hasMany(HorarioMedicosController::class, 'id_doctor');
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
