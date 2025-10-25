<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitasController;
use App\Http\Controllers\ConsultorioController;
use App\Http\Controllers\RecepcionistaController;
use App\Http\Controllers\EspecialidadesController;
use App\Http\Controllers\DoctoresController;
use App\Http\Controllers\EpsController;
use App\Http\Controllers\HorarioMedicosController;
use App\Http\Controllers\PacientesController;
use Illuminate\Support\Facades\Route;


// AUTENTICACIÓN GENERAL
Route::post('/login', [AuthController::class, 'login']); //login
Route::post('/logout', [AuthController::class, 'logout']); //Logout

// REGISTROS
Route::post('registrarDoctor', [DoctoresController::class, 'registrarDoctor']); //registro doctor
Route::post('registrarPaciente', [PacientesController::class, 'registrarPaciente']); //registro paciente
Route::get('/listarEpsPublico', [EpsController::class, 'listarEps']); //listar eps

Route::post('registrarRecepcionista', [RecepcionistaController::class, 'registrarRecepcionista']); //registro recepcionista

//RECUPERACIÓN DE CONTRASEÑA
Route::post('/password/forgot', [AuthController::class, 'sendResetLink']); //solicitar token de reseteo de contraseña
Route::post('/password/reset', [AuthController::class, 'resetPassword']); //resetear la contraseña

// PACIENTES
Route::middleware(['auth:paciente'])->group(function () {
    Route::get('/me/paciente', [AuthController::class, 'mePaciente']); //perfil del paciente
    Route::put('/editar/me/paciente', [PacientesController::class, 'updatePacientePerfil']); //Actualizar perfil
    Route::get('/citas/historial-paciente', [PacientesController::class, 'HistorialCitas']); //Historial de citass
    Route::get('/citas/proximas/pendientes', [PacientesController::class, 'ProximasCitasPendientes']); //proximas citasRoute
    Route::get('/citas/proximas/confirmadas', [PacientesController::class, 'ProximasCitasConfirmadas']); //proximas citasRoute
    Route::put('/paciente/citas/{id}/estado', [PacientesController::class, 'CancelarCita']); //eliminar cuenta
    Route::put('/paciente/citas/{id}/reprogramar', [CitasController::class, 'reprogramarCita']); // ✅ NUEVO: Reprogramar cita

    Route::get('contarPacientes', [PacientesController::class, 'contarPacientes']); //contar pacientes
    Route::post('/paciente/change-password', [PacientesController::class, 'changePassword']); //cambiar clave
    Route::delete('/eliminarCuenta/paciente', [PacientesController::class, 'deleteAccount']); //eliminar cuenta

    Route::post('/paciente/crear-cita', [CitasController::class, 'storePaciente']);
    Route::post('/paciente/crear-cita"', [CitasController::class, 'storePaciente']);
    Route::get('/disponibilidad/agendar-cita/{id}', [CitasController::class, 'disponibilidad']);
    Route::get('/listar/doctores/agendar-cita', [DoctoresController::class, 'index']);

    Route::post('/paciente/guardar-token', [PacientesController::class, 'guardarTokenNotificacion']);
    Route::get('/probar-notificaciones', [CitasController::class, 'verificarCambioEstado']);
});

// DOCTORES
Route::middleware(['auth:doctor'])->group(function () {
    Route::get('/me/doctor', [AuthController::class, 'meDoctor']); //perfil doctor
    Route::get('/doctor/estadisticas', [DoctoresController::class, 'estadisticas']); //estadiscas
    Route::get('/doctor/mis-pacientes', [DoctoresController::class, 'misPacientes']); //mis pacientes
    Route::get('/doctor/pacientes/{pacienteId}/historial', [DoctoresController::class, 'historialPaciente']); //historial paciente

    Route::get('/doctor/citas/hoy', [DoctoresController::class, 'citasHoyDoctor']);
    Route::put('/doctor/citas/{id}/realizada', [DoctoresController::class, 'marcarComoRealizada']);

    Route::put('/me/doctor', [DoctoresController::class, 'updateMedicoPerfil']); //editar perfil
    Route::delete('/eliminarCuenta/medico', [DoctoresController::class, 'deleteAccount']); // Eliminar cuenta de doctor

    // Gestión de Horario del Doctor
    Route::get('/ListarHorario', [HorarioMedicosController::class, 'listarHorarios']); // Ver mi horario
    Route::post('/CrearHorarios', [HorarioMedicosController::class, 'store']); // Crear horario
    Route::put('/EditarHorario/{id}', [HorarioMedicosController::class, 'update']);
    Route::delete('/EliminarHorario/{id}', [HorarioMedicosController::class, 'destroy']);
});

// RECEPCIONISTAS
Route::middleware(['auth:recepcionista'])->group(function () {
    Route::get('/doctores', [DoctoresController::class, 'index']); //listar doctores
    Route::get('paciente', [PacientesController::class, 'index']); //listar pacientes
    Route::get('/listarEspecialidades', [EspecialidadesController::class, 'index']); //listar especialidades


    Route::get('/me/recepcionista', [AuthController::class, 'meRecepcionista']); //perfil recepcionista
    Route::get('/pacientes/documento/{documento}', [PacientesController::class, 'buscarPorDocumento']); //buscar paciente por documento
    Route::post('/citas', [CitasController::class, 'store']);  //Crear cita
    Route::get('/pacientes/buscar', [PacientesController::class, 'buscar']); //buscar pacientes por nombre o documento
    Route::get('/recepcion/estadisticas', [CitasController::class, 'estadisticasRecepcion']); //estadisticas dashboard recepcion
    Route::get('/citas-hoy-recepcion', [CitasController::class, 'citasHoyRecepcion']); //citas de hoy para recepcionista
    Route::put('/citas/{id}/estado', [CitasController::class, 'actualizarEstado']); //actualizar estado de la cita
    Route::get('/citas/hoy', [CitasController::class, 'citasHoyRecepcion']); //citas de hoy


    Route::get('/especialidades', [EspecialidadesController::class, 'index']); //listar especialidades
    Route::get('/listardoc', [DoctoresController::class, 'listardoc']); //listar doctores con especialidades
    Route::post('/AgregarDoctores', [DoctoresController::class, 'store']); //crear doctores

    Route::get('/listardoctores/{id}', [DoctoresController::class, 'listardoctores']); //listar un doctor con especialidades
    Route::put('/editardoctores/{id}', [DoctoresController::class, 'editardoctores']); // editar un doctor con especialidad

    Route::get('/listarespecialidades', [DoctoresController::class, 'listarespecialidades']); //listar especialidades de doctores
    Route::post('/crearEspecialidades', [EspecialidadesController::class, 'store']); //crear especialidades
    Route::get('/especialidades/{id}', [EspecialidadesController::class, 'show']); //mostrar especialidad por id
    Route::put('/especialidades/{id}', [EspecialidadesController::class, 'update']); //actualizar especialidad por id
    Route::delete('/especialidades/{id}', [EspecialidadesController::class, 'destroy']); //eliminar especialidad por id


    Route::get('/listarConsultorios', [ConsultorioController::class, 'listarConsultorios']); //listar consultorios
    Route::get('/consultoriosDisponibles', [ConsultorioController::class, 'listarConsultoriosDisponibles']); //listar solo consultorios disponibles
    Route::post('/CrearConsultorios', [ConsultorioController::class, 'store']); //crear consultorio
    Route::put('/EditarConsultorios/{id}', [ConsultorioController::class, 'update']); //Editar consultorio
    Route::get('/consultorioByID/{id}', [ConsultorioController::class, 'consultorioByID']); // consultorio por id
    Route::delete('/EliminarConsultorio/{id}', [ConsultorioController::class, 'destroy']); // Eliminar consultorio


    Route::get('/listarEps', [EpsController::class, 'listarEps']); //listar eps
    Route::post('/CrearEps', [EpsController::class, 'CrearEps']); //crear eps
    Route::get('/EpsByID/{id}', [EpsController::class, 'EpsByID']); // Eps por id
    Route::put('/EditarEps/{id}', [EpsController::class, 'update']); //Editar eps
    Route::delete('/EliminarEps/{id}', [EpsController::class, 'destroy']); // Eliminar eps

    Route::get('/citas/pendientes', [CitasController::class, 'citasPendientes']);
    Route::put('/citas/{id}/estado', [CitasController::class, 'actualizarEstado']);

    Route::put('/doctores/{id}', [DoctoresController::class, 'update']); //Actualizar doctor
    Route::delete('/doctores/{id}', [DoctoresController::class, 'destroy']); //Eliminar doctor
    Route::put('/me/recepcionista', [RecepcionistaController::class, 'updateRecepcionPerfil']); //editar perfil
    Route::delete('/eliminarCuenta/recepcionista', [RecepcionistaController::class, 'deleteAccount']); // Eliminar cuenta de doctor
    Route::post('/recepcionista/change-password', [RecepcionistaController::class, 'changePassword']); //cambiar clave

    Route::post('/crearCita', [RecepcionistaController::class, 'storeCitaRecepcion']); //Agendar cita
    Route::get('/disponibilidad/{id}', [CitasController::class, 'disponibilidad']); //verificar disponibilidad de doctor
    Route::get('/listarDoctores', [CitasController::class, 'listarDoctores']); //listar doctores con especialidades

});
