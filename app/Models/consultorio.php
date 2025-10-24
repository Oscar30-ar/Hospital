<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Es buena práctica agregarlo
use Illuminate\Database\Eloquent\Model;

class Consultorio extends Model // <-- CAMBIO: Nombre de la clase en PascalCase
{
    use HasFactory; // <-- BUENA PRÁCTICA

    protected $fillable = [
        'nombre',
        'id_doctor',
    ];

    public function doctor(){
        return $this->hasOne((Doctores::class),'id_doctor');
    }
}
