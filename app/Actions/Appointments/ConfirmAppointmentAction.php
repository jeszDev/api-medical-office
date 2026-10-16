<?php

declare(strict_types=1);

namespace App\Actions\Appointments;

use App\Events\Appointments\AppointmentScheduled;
use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ConfirmAppointmentAction
{
    /**
     * Confirma una cita médica asignándole fecha, hora y verificando disponibilidad de agenda.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(Appointment $appointment, array $data, ?User $causer = null): Appointment
    {
        if ($appointment->cita_estatus_id === CatalogAppointmentStatus::CANCELADA || $appointment->cita_estatus_id === CatalogAppointmentStatus::ATENDIDA) {
            throw ValidationException::withMessages([
                'appointment' => ['No es posible confirmar una cita médica que ya ha sido cancelada o atendida.'],
            ]);
        }

        $doctorId = ! empty($data['medico_id']) ? (int) $data['medico_id'] : (int) $appointment->medico_id;
        $startDateRaw = $data['fecha_hora_inicio'] ?? $appointment->fecha_hora_inicio;
        $endDateRaw = $data['fecha_hora_termino'] ?? $appointment->fecha_hora_termino;

        if (empty($startDateRaw) || empty($endDateRaw)) {
            throw ValidationException::withMessages([
                'fecha_hora_inicio' => ['Debe proporcionar fecha y hora de inicio y término para confirmar la cita.'],
            ]);
        }

        $startDate = Carbon::parse($startDateRaw)->format('Y-m-d H:i:s');
        $endDate = Carbon::parse($endDateRaw)->format('Y-m-d H:i:s');

        // Validar colisión de horarios con otras citas activas del médico
        $hasConflict = Appointment::query()
            ->where('id', '!=', $appointment->id)
            ->where('medico_id', $doctorId)
            ->whereIn('cita_estatus_id', [
                CatalogAppointmentStatus::PENDIENTE,
                CatalogAppointmentStatus::CONFIRMADA,
                CatalogAppointmentStatus::EN_SALA_DE_ESPERA,
            ])
            ->whereNotNull('fecha_hora_inicio')
            ->whereNotNull('fecha_hora_termino')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('fecha_hora_inicio', '<', $endDate)
                    ->where('fecha_hora_termino', '>', $startDate);
            })
            ->exists();

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'fecha_hora_inicio' => ['Ya existe una cita programada para el médico en ese rango de horario.'],
            ]);
        }

        $appointment->update([
            'fecha_hora_inicio' => $startDate,
            'fecha_hora_termino' => $endDate,
            'medico_id' => $doctorId,
            'observaciones' => $data['observaciones'] ?? $appointment->observaciones,
            'cita_estatus_id' => CatalogAppointmentStatus::CONFIRMADA,
        ]);

        $appointment->load(['doctor', 'patients', 'status']);

        activity('citas')
            ->performedOn($appointment)
            ->causedBy($causer)
            ->withProperties([
                'folio' => $appointment->folio,
                'medico_id' => $doctorId,
                'fecha_hora_inicio' => $startDate,
                'fecha_hora_termino' => $endDate,
                'cita_estatus_id' => CatalogAppointmentStatus::CONFIRMADA,
            ])
            ->log('Cita médica confirmada con horario asignado');

        event(new AppointmentScheduled($appointment));

        return $appointment;
    }
}
