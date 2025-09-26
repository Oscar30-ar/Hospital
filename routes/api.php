<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitasController;
use App\Http\Controllers\RecepcionistaController;
use App\Http\Controllers\EspecialidadesController;
use App\Http\Controllers\DoctoresController;
use App\Http\Controllers\PacientesController;
use Illuminate\Support\Facades\Route;


// AUTENTICACIÓN GENERAL
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
// REGISTROS
Route::post('registrarDoctor', [DoctoresController::class, 'registrarDoctor']);
Route::post('registrarPaciente', [PacientesController::class, 'registrarPaciente']);
Route::post('registrarRecepcionista', [RecepcionistaController::class, 'registrarRecepcionista']);
Route::post('crearEspecialidades', [EspecialidadesController::class, 'store']);

// PACIENTES
Route::middleware(['auth:paciente'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('contarPacientes', [PacientesController::class, 'contarPacientes']);
    Route::get('listarCitasDePaciente/{id}', [PacientesController::class, 'listarCitasDePaciente']);
    Route::get('listarHombres', [PacientesController::class, 'listarHombres']);
    Route::get('listarPaciente', [PacientesController::class, 'index']);
    Route::put('editarPaciente/{id}', [PacientesController::class, 'update']);
    Route::delete('eliminarPaciente/{id}', [PacientesController::class, 'destroy']);
});

// DOCTORES
Route::middleware(['auth:doctor'])->group(function () {
    Route::get('buscarDoctorPorCedula/{documento}', [DoctoresController::class, 'buscarDoctorPorCedula']);
    Route::get('listarDoctores', [DoctoresController::class, 'index']);
    Route::put('editarDoctor/{id}', [DoctoresController::class, 'update']);
    Route::delete('eliminarDoctor/{id}', [DoctoresController::class, 'destroy']);
});

// RECEPCIONISTAS
Route::middleware(['auth:recepcionista'])->group(function () {
    Route::get('listarRecepcionista', [RecepcionistaController::class, 'index']);
    Route::put('editarRecepcionista/{id}', [RecepcionistaController::class, 'update']);
    Route::delete('eliminarRecepcionista/{id}', [RecepcionistaController::class, 'destroy']);
});

// ESPECIALIDADES
Route::middleware(['auth:doctor'])->group(function () {
    Route::get('listarEspecialidades', [EspecialidadesController::class, 'index']);
    Route::put('editarEspecialidades/{id}', [EspecialidadesController::class, 'update']);
    Route::delete('eliminarEspecialidades/{id}', [EspecialidadesController::class, 'destroy']);
});

// CITAS
Route::middleware(['auth:paciente'])->group(function () {
    Route::get('totalCitas', [CitasController::class, 'totalCitas']);
    Route::get('listarCitas', [CitasController::class, 'index']);
    Route::post('crearCitas', [CitasController::class, 'store']);
    Route::put('editarCitas/{id}', [CitasController::class, 'update']);
    Route::delete('eliminarCitas/{id}', [CitasController::class, 'destroy']);
});
