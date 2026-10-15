<?php

declare(strict_types=1);

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateAppointmentAction
{
    /**
     * Ejecuta el proceso de negocio para crear una cita médica.
     *
     * @param  array<string, mixed>  $data
     * @throws ValidationException
     */
    public function execute(array $data): Appointment
    {
        $startDate = Carbon::parse($data['fecha_hora_inicio'])->format('Y-m-d H:i:s');
        $endDate = Carbon::parse($data['fecha_hora_termino'])->format('Y-m-d H:i:s');
        $doctorId = (int) $data['medico_id'];

        // 1. Validar colisión de horarios para el médico (citas activas)
        $hasConflict = Appointment::query()
            ->where('medico_id', $doctorId)
            ->whereIn('cita_estatus_id', [
                CatalogAppointmentStatus::PENDIENTE,
                CatalogAppointmentStatus::CONFIRMADA,
            ])
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

        // 2. Persistir en base de datos mediante transacción atómica
        $appointment = DB::transaction(function () use ($data) {
            $appointment = Appointment::create([
                'fecha_hora_inicio' => $data['fecha_hora_inicio'],
                'fecha_hora_termino' => $data['fecha_hora_termino'],
                'motivo' => $data['motivo'] ?? null,
                'observaciones_cita' => $data['observaciones_cita'] ?? null,
                'medico_id' => $data['medico_id'],
                'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
            ]);

            $pivotAttributes = [];
            if (!empty($data['observaciones_paciente'])) {
                $pivotAttributes['observaciones'] = $data['observaciones_paciente'];
            }

            $appointment->patients()->attach($data['paciente_id'], $pivotAttributes);

            return $appointment;
        });

        // 3. Eager loading de relaciones para evitar problemas de N+1 queries al serializar
        $appointment->load(['doctor', 'patients', 'status']);

        // 4. Registro de auditoría clínica con Spatie Activitylog
        activity('citas')
            ->performedOn($appointment)
            ->causedBy(auth()->user())
            ->withProperties([
                'medico_id' => $appointment->medico_id,
                'paciente_id' => $data['paciente_id'],
                'fecha_hora_inicio' => $appointment->fecha_hora_inicio,
                'fecha_hora_termino' => $appointment->fecha_hora_termino,
                'motivo' => $appointment->motivo,
            ])
            ->log('Cita médica agendada');

        // 5. (Punto de extensión futuro: Disparar evento para enviar email o WhatsApp)
        // event(new AppointmentScheduled($appointment));

        return $appointment;
    }
}
