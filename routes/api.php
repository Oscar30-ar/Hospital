<?php

use App\Http\Controllers\CitasController;
use App\Http\Controllers\RecepcionistaController;
use App\Http\Controllers\EspecialidadesController;
use App\Http\Controllers\DoctoresController;
use App\Http\Controllers\PacientesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Doctores
Route::get('listarDoctores',[DoctoresController::class,'index']);
Route::post('crearDoctor',[DoctoresController::class,'store']);
Route::put('editarDoctor/{id}',[DoctoresController::class,'update']);
Route::delete('eliminarDoctor/{id}',[DoctoresController::class,'destroy']);

//Pacientes
Route::get('listarPaciente',[PacientesController::class,'index']);
Route::post('crearPaciente',[PacientesController::class,'store']);
Route::put('editarPaciente/{id}',[PacientesController::class,'update']);
Route::delete('eliminarPaciente/{id}',[PacientesController::class,'destroy']);

//Especialidades
Route::get('listarEspecialidades',[EspecialidadesController::class,'index']);
Route::post('crearEspecialidades',[EspecialidadesController::class,'store']);
Route::put('editarEspecialidades/{id}',[EspecialidadesController::class,'update']);
Route::delete('eliminarEspecialidades/{id}',[EspecialidadesController::class,'destroy']);

//Recepcionista
Route::get('listarRecepcionista',[RecepcionistaController::class,'index']);
Route::post('crearRecepcionista',[RecepcionistaController::class,'store']);
Route::put('editarRecepcionista/{id}',[RecepcionistaController::class,'update']);
Route::delete('eliminarRecepcionista/{id}',[RecepcionistaController::class,'destroy']);

//Citas
Route::get('listarCitas',[CitasController::class,'index']);
Route::post('crearCitas',[CitasController::class,'store']);
Route::put('editarCitas/{id}',[CitasController::class,'update']);
Route::delete('eliminarCitas/{id}',[CitasController::class,'destroy']);