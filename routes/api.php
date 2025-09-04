<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitasController;
use App\Http\Controllers\RecepcionistaController;
use App\Http\Controllers\EspecialidadesController;
use App\Http\Controllers\DoctoresController;
use App\Http\Controllers\PacientesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas de autenticación y registro
Route::post('login', [AuthController::class, 'login']);

// Rutas de registro (creación)
Route::post('crearDoctor',[DoctoresController::class,'store']);
Route::post('crearPaciente',[PacientesController::class,'store']);
Route::post('crearRecepcionista',[RecepcionistaController::class,'store']);
Route::post('crearEspecialidades',[EspecialidadesController::class,'store']);

// Agrupa todas las demás rutas para protegerlas con JWT
Route::middleware('jwt.auth')->group(function () {
    // Ruta de cierre de sesión
    Route::post('logout', [AuthController::class, 'logout']);

    // Doctores
    Route::get('buscarDoctorPorCedula/{documento}',[DoctoresController::class,'buscarDoctorPorCedula']);
    Route::get('listarDoctores',[DoctoresController::class,'index']);
    Route::put('editarDoctor/{id}',[DoctoresController::class,'update']);
    Route::delete('eliminarDoctor/{id}',[DoctoresController::class,'destroy']);

    // Pacientes
    Route::get('contarPacientes',[PacientesController::class,'contarPacientes']);
    Route::get('listarCitasDePaciente/{id}',[PacientesController::class,'listarCitasDePaciente']);
    Route::get('listarHombres',[PacientesController::class,'listarHombres']);
    Route::get('listarPaciente',[PacientesController::class,'index']);
    Route::put('editarPaciente/{id}',[PacientesController::class,'update']);
    Route::delete('eliminarPaciente/{id}',[PacientesController::class,'destroy']);

    // Especialidades
    Route::get('listarEspecialidades',[EspecialidadesController::class,'index']);
    Route::put('editarEspecialidades/{id}',[EspecialidadesController::class,'update']);
    Route::delete('eliminarEspecialidades/{id}',[EspecialidadesController::class,'destroy']);

    // Recepcionista
    Route::get('listarRecepcionista',[RecepcionistaController::class,'index']);
    Route::put('editarRecepcionista/{id}',[RecepcionistaController::class,'update']);
    Route::delete('eliminarRecepcionista/{id}',[RecepcionistaController::class,'destroy']);

    // Citas
    Route::get('totalCitas',[CitasController::class,'totalCitas']);
    Route::get('listarCitas',[CitasController::class,'index']);
    Route::post('crearCitas',[CitasController::class,'store']);
    Route::put('editarCitas/{id}',[CitasController::class,'update']);
    Route::delete('eliminarCitas/{id}',[CitasController::class,'destroy']);
});