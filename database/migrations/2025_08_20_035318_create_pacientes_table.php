<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->integer('documento')->unique();
            $table->string('correo')->unique();
            $table->string('clave');
            $table->string('celular');
            $table->date('fecha_nacimiento');
            $table->string('ciudad');
            $table->foreignId('id_eps')->constrained('eps')->onDelete('cascade');
            $table->enum('Rh', [
                'A+',
                'A-',
                'B+',
                'B-',
                'AB+',
                'AB-',
                'O+',
                'O-'
            ])->nullable();
            $table->enum('genero', ['Masculino', 'Femenino']);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
