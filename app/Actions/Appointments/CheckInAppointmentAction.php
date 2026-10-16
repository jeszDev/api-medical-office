<?php

declare(strict_types=1);

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CheckInAppointmentAction
{
    /**
     * Registra la llegada presencial del paciente a la clínica y lo sitúa en sala de espera.
     * Válido tanto si la cita estaba en estatus PENDIENTE como CONFIRMADA.
     *
     * @throws ValidationException
     */
    public function execute(Appointment $appointment, ?User $causer = null): Appointment
    {
        if (in_array($appointment->cita_estatus_id, [
            CatalogAppointmentStatus::CANCELADA,
            CatalogAppointmentStatus::ATENDIDA,
            CatalogAppointmentStatus::NO_ASISTIO,
        ], true)) {
            throw ValidationException::withMessages([
                'appointment' => ['No es posible registrar llegada a sala de espera para una cita en su estado actual.'],
            ]);
        }

        $appointment->update([
            'cita_estatus_id' => CatalogAppointmentStatus::EN_SALA_DE_ESPERA,
        ]);

        $appointment->load(['doctor', 'patients', 'status']);

        activity('citas')
            ->performedOn($appointment)
            ->causedBy($causer)
            ->withProperties([
                'folio' => $appointment->folio,
                'medico_id' => $appointment->medico_id,
                'estatus_anterior' => $appointment->getOriginal('cita_estatus_id'),
                'nuevo_estatus' => CatalogAppointmentStatus::EN_SALA_DE_ESPERA,
            ])
            ->log('Paciente en sala de espera (Check-in presencial)');

        return $appointment;
    }
}
