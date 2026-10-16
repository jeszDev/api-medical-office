<?php

declare(strict_types=1);

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class NoShowAppointmentAction
{
    /**
     * Marca una cita médica como 'No asistió' cuando el paciente no se presenta tras el tiempo de tolerancia.
     *
     * @throws ValidationException
     */
    public function execute(Appointment $appointment, ?User $causer = null): Appointment
    {
        if (in_array($appointment->cita_estatus_id, [
            CatalogAppointmentStatus::CANCELADA,
            CatalogAppointmentStatus::ATENDIDA,
        ], true)) {
            throw ValidationException::withMessages([
                'appointment' => ['No es posible marcar como inasistencia una cita que ya fue atendida o cancelada.'],
            ]);
        }

        $appointment->update([
            'cita_estatus_id' => CatalogAppointmentStatus::NO_ASISTIO,
        ]);

        $appointment->load(['doctor', 'patients', 'status']);

        activity('citas')
            ->performedOn($appointment)
            ->causedBy($causer)
            ->withProperties([
                'folio' => $appointment->folio,
                'medico_id' => $appointment->medico_id,
                'nuevo_estatus' => CatalogAppointmentStatus::NO_ASISTIO,
            ])
            ->log('Cita médica marcada como no asistió');

        return $appointment;
    }
}
