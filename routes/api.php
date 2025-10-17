<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitasController;
use App\Http\Controllers\RecepcionistaController;
use App\Http\Controllers\EspecialidadesController;
use App\Http\Controllers\DoctoresController;
use App\Http\Controllers\PacientesController;
use App\Models\Recepcionista;
use Illuminate\Support\Facades\Route;


// AUTENTICACIÓN GENERAL
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
// REGISTROS
Route::post('registrarDoctor', [DoctoresController::class, 'registrarDoctor']);
Route::post('registrarPaciente', [PacientesController::class, 'registrarPaciente']);
Route::post('registrarRecepcionista', [RecepcionistaController::class, 'registrarRecepcionista']);
Route::post('crearEspecialidades', [EspecialidadesController::class, 'store']);
Route::post('/password/forgot', [AuthController::class, 'sendResetLink']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

// PACIENTES
Route::middleware(['auth:paciente'])->group(function () {
    Route::get('/me/paciente', [AuthController::class, 'mePaciente']); //perfil del paciente
    Route::put('/me/paciente', [PacientesController::class, 'updatePacientePerfil']); //Actualizar perfil
    Route::get('/citas/historial-paciente', [PacientesController::class, 'HistorialCitas']); //Historial de citass
    Route::get('/citas/proximas', [PacientesController::class, 'ProximasCitas']); //proximas citasRoute::post('change-password', [UserController::class, 'changePassword']);
    Route::get('contarPacientes', [PacientesController::class, 'contarPacientes']); //contar pacientes
    Route::post('change-password', [PacientesController::class, 'changePassword']); //cambiar clave
    Route::delete('eliminarCuenta', [PacientesController::class, 'deleteAccount']); //eliminar cuenta

    // Route::get('listarCitasDePaciente/{id}', [PacientesController::class, 'listarCitasDePaciente']);
    // Route::get('listarHombres', [PacientesController::class, 'listarHombres']);
    // Route::get('listarPaciente', [PacientesController::class, 'index']);
    // Route::delete('eliminarPaciente/{id}', [PacientesController::class, 'destroy']);
});

// DOCTORES
Route::middleware(['auth:doctor'])->group(function () {
    Route::get('/me/doctor', [AuthController::class, 'meDoctor']); //perfil doctor
    Route::get('/doctor/estadisticas', [DoctoresController::class, 'estadisticas']); //estadiscas
    Route::get('/doctor/pacientes', [DoctoresController::class, 'misPacientes']);
    Route::get('/doctor/pacientes/{pacienteId}/historial', [DoctoresController::class, 'historialPaciente']); //historial paciente
    Route::get('doctor/citas', [DoctoresController::class, 'citasHoy']); //citas Hoy
    Route::put('/me/doctor', [DoctoresController::class, 'updateMedicoPerfil']); //editar perfil
    Route::delete('/eliminarCuenta', [DoctoresController::class, 'deleteAccount']); // Eliminar cuenta de doctor
    Route::post('/doctor/change-password', [DoctoresController::class, 'changePasswordMedico']); //cambiar clave

    // Route::get('buscarDoctorPorCedula/{documento}', [DoctoresController::class, 'buscarDoctorPorCedula']);
    // Route::get('listarDoctores', [DoctoresController::class, 'index']);
    // Route::put('editarDoctor/{id}', [DoctoresController::class, 'update']);
    // Route::delete('eliminarDoctor/{id}', [DoctoresController::class, 'destroy']);
});

// RECEPCIONISTAS
Route::middleware(['auth:recepcionista'])->group(function () {

    Route::get('/me/recepcionista', [AuthController::class, 'meRecepcionista']);
    Route::get('doctores', [DoctoresController::class, 'index']);
    Route::get('paciente', [PacientesController::class, 'index']);

    // Funcionalidades de Gestión de Citas (para GestionCitasScreen.js)
    Route::get('/me/recepcionista', [AuthController::class, 'meRecepcionista']);
    Route::get('/pacientes/documento/{documento}', [PacientesController::class, 'buscarPorDocumento']);
    Route::get('/doctores', [DoctoresController::class, 'index']);     // plural
    Route::post('/citas', [CitasController::class, 'store']);  // **CREAR CITA** (usado por `crearCita` en el frontend)
    Route::get('/pacientes/buscar', [PacientesController::class, 'buscar']);
    Route::get('/recepcion/estadisticas', [CitasController::class, 'estadisticasRecepcion']);
    Route::get('/citas-hoy-recepcion', [CitasController::class, 'citasHoyRecepcion']);
    Route::put('/citas/{id}/estado', [CitasController::class, 'actualizarEstado']);
    Route::get('/citas/hoy', [CitasController::class, 'citasHoyRecepcion']);

    Route::get('/especialidades', [EspecialidadesController::class, 'index']);
    Route::get('/doctores', [DoctoresController::class, 'index']);
    Route::get('/listardoc', [DoctoresController::class, 'listardoc']);
    Route::post('/doctores', [DoctoresController::class, 'store']);
    
    Route::get('/listardoctores/{id}', [DoctoresController::class, 'listardoctores']);
    Route::put('/editardoctores/{id}', [DoctoresController::class, 'editardoctores']);
    Route::get('/listarespecialidades', [DoctoresController::class, 'listarespecialidades']);




    Route::put('/doctores/{id}', [DoctoresController::class, 'update']);
    Route::delete('/doctores/{id}', [DoctoresController::class, 'destroy']);
    Route::put('/me/recepcionista', [RecepcionistaController::class, 'updateRecepcionPerfil']); //editar perfil
    Route::delete('/eliminarCuenta', [RecepcionistaController::class, 'deleteAccount']); // Eliminar cuenta de doctor
    Route::post('change-password', [RecepcionistaController::class, 'changePassword']); //cambiar clave


    // Route::get('listarRecepcionista', [RecepcionistaController::class, 'index']);
    // Route::put('editarRecepcionista/{id}', [RecepcionistaController::class, 'update']);
    // Route::delete('eliminarRecepcionista/{id}', [RecepcionistaController::class, 'destroy']);
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
