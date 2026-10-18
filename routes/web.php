<?php

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\Appointments\AppointmentCancelledNotification;
use App\Notifications\Appointments\AppointmentReminderNotification;
use App\Notifications\Appointments\AppointmentScheduledNotification;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

if (app()->environment('local', 'testing')) {
    Route::get('/dev/email-preview/{type}', function (string $type) {
        $doctor = new User([
            'nombre' => 'Roberto',
            'primer_apellido' => 'Sánchez',
            'segundo_apellido' => 'Morales',
            'email' => 'dr.sanchez@starmedica-ejemplo.com',
        ]);
        $doctor->id = 1;

        $patient = new Patient([
            'nombre' => 'Ana Laura',
            'primer_apellido' => 'González',
            'segundo_apellido' => 'Ramos',
            'correo_electronico' => 'ana.gonzalez@ejemplo.com',
            'telefono' => '+52 55 1234 5678',
        ]);
        $patient->id = 101;

        $appointment = new Appointment([
            'folio' => 'CIT-2026-0001',
            'fecha_hora_inicio' => now()->addDay()->setHour(10)->setMinute(30)->setSecond(0)->toDateTimeString(),
            'fecha_hora_termino' => now()->addDay()->setHour(11)->setMinute(15)->setSecond(0)->toDateTimeString(),
            'motivo' => 'Revisión Cardiológica y Electrocardiograma',
            'medico_id' => 1,
            'cita_estatus_id' => 1,
        ]);
        $appointment->id = 1042;
        $appointment->setRelation('doctor', $doctor);
        $appointment->setRelation('patients', collect([$patient]));

        return match ($type) {
            'scheduled' => (new AppointmentScheduledNotification($appointment))->toMail($patient)->render(),
            'cancelled' => (new AppointmentCancelledNotification($appointment, 'El especialista fue convocado a una cirugía de emergencia no programada.'))->toMail($patient)->render(),
            'reminder' => (new AppointmentReminderNotification($appointment))->toMail($patient)->render(),
            default => abort(404, "Tipo de plantilla desconocido. Usa 'scheduled', 'cancelled' o 'reminder'."),
        };
    })->name('dev.email-preview');
}
