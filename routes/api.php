<?php

use App\Http\Controllers\AppointmentConsultationController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PatientAppointmentController;
use App\Http\Controllers\PatientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/check-status', [AuthController::class, 'checkStatus'])->middleware('auth:sanctum');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::apiResource('patients', PatientController::class)->middleware('auth:sanctum');

Route::apiResource('appointments', AppointmentController::class);
Route::patch('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

Route::apiResource('patients.appointments', PatientAppointmentController::class);
// Route::apiResource('patients.appointments', AppointmentController::class)->shallow();

Route::apiResource('consultations', ConsultationController::class);

Route::apiResource('appointments.consultations', AppointmentConsultationController::class);
