<?php

use App\Http\Controllers\AppointmentConsultationController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicSettingController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ConsultationPrescriptionController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\MyPatientController;
use App\Http\Controllers\PatientAppointmentController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientGuardianController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/check-status', [AuthController::class, 'checkStatus'])->middleware('auth:sanctum');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Gestión de perfil familiar y dependientes (Autoservicio para el usuario/paciente)
    Route::get('my-patients', [MyPatientController::class, 'index'])->name('my-patients.index');
    Route::post('my-patients', [MyPatientController::class, 'store'])->name('my-patients.store');

    Route::apiResource('patients', PatientController::class);

    // Gestión de tutores y vinculaciones familiares por parte del personal clínico
    Route::get('patients/{patient}/guardians', [PatientGuardianController::class, 'index'])->name('patients.guardians.index');
    Route::post('patients/{patient}/guardians', [PatientGuardianController::class, 'store'])->name('patients.guardians.store');

    Route::post('appointments/request', [AppointmentController::class, 'requestAppointment'])->name('appointments.request');
    Route::patch('appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::patch('appointments/{appointment}/check-in', [AppointmentController::class, 'checkIn'])->name('appointments.check-in');
    Route::patch('appointments/{appointment}/no-show', [AppointmentController::class, 'noShow'])->name('appointments.no-show');
    Route::patch('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::apiResource('appointments', AppointmentController::class);

    Route::apiResource('patients.appointments', PatientAppointmentController::class);

    // Perfil profesional y cédula del médico
    Route::get('doctor/profile', [DoctorProfileController::class, 'show'])->name('doctor.profile.show');
    Route::post('doctor/profile', [DoctorProfileController::class, 'update'])->name('doctor.profile.update');
    Route::put('doctor/profile', [DoctorProfileController::class, 'update'])->name('doctor.profile.put');

    // Configuración institucional de la clínica
    Route::get('clinic/settings', [ClinicSettingController::class, 'show'])->name('clinic.settings.show');
    Route::post('clinic/settings', [ClinicSettingController::class, 'update'])->name('clinic.settings.update');
    Route::put('clinic/settings', [ClinicSettingController::class, 'update'])->name('clinic.settings.put');

    Route::apiResource('consultations', ConsultationController::class);
    Route::post('consultations/{consultation}/prescription', [ConsultationPrescriptionController::class, 'store'])->name('consultations.prescription.store');
    Route::get('consultations/{consultation}/prescription', [ConsultationPrescriptionController::class, 'show'])->name('consultations.prescription.show');

    Route::apiResource('appointments.consultations', AppointmentConsultationController::class);
});
