<?php

declare(strict_types=1);

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CancelAppointmentAction
{
    /**
     * Ejecuta el proceso de negocio para cancelar una cita médica y registrar la auditoría.
     *
     * @throws ValidationException
     */
    public function execute(Appointment $appointment, User $user, string $reason): Appointment
    {
        // 1. Reglas de negocio: Validar estado actual de la cita
        if ($appointment->cita_estatus_id === CatalogAppointmentStatus::CANCELADA) {
            throw ValidationException::withMessages([
                'appointment' => ['La cita ya se encuentra cancelada.'],
            ]);
        }

        if ($appointment->cita_estatus_id === CatalogAppointmentStatus::ATENDIDA) {
            throw ValidationException::withMessages([
                'appointment' => ['No es posible cancelar una cita médica que ya ha sido atendida.'],
            ]);
        }

        $appointment->loadMissing('status');
        $oldStatusName = $appointment->status?->nombre ?? 'Desconocido';
        $oldStatusId = $appointment->cita_estatus_id;

        // 2. Transición de estado a CANCELADA
        $appointment->cita_estatus_id = CatalogAppointmentStatus::CANCELADA;
        $appointment->save();

        // 3. Registro de auditoría clínica con Spatie Activitylog
        activity('citas')
            ->performedOn($appointment)
            ->causedBy($user)
            ->withProperties([
                'estatus_anterior_id' => $oldStatusId,
                'estatus_anterior' => $oldStatusName,
                'nuevo_estatus_id' => CatalogAppointmentStatus::CANCELADA,
                'nuevo_estatus' => 'Cancelada',
                'motivo_cancelacion' => $reason,
            ])
            ->log('Cita médica cancelada');

        // 4. Eager loading para evitar N+1 queries al serializar en el Resource
        $appointment->load(['doctor', 'patients', 'status']);

        // 5. Punto de extensión futuro (ej. notificar al paciente por WhatsApp sobre la cancelación)
        // event(new AppointmentCancelled($appointment, $reason));

        return $appointment;
    }
}
