<?php

declare(strict_types=1);

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequestAppointmentAction
{
    /**
     * Ejecuta el proceso de solicitud de cita por parte de un paciente o tutor.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(User $user, array $data): Appointment
    {
        $patientId = (int) $data['paciente_id'];

        // Si el usuario es un paciente/tutor, validar que el paciente pertenezca a su núcleo familiar
        if ($user->hasRole('patient')) {
            $isLinked = $user->patients()
                ->where('pacientes.id', $patientId)
                ->exists();

            if (! $isLinked) {
                throw ValidationException::withMessages([
                    'paciente_id' => ['El paciente indicado no se encuentra vinculado a su cuenta familiar.'],
                ]);
            }
        }

        $appointment = DB::transaction(function () use ($data, $patientId): Appointment {
            $appointment = Appointment::create([
                'fecha_hora_inicio' => null,
                'fecha_hora_termino' => null,
                'fecha_preferida' => $data['fecha_preferida'] ?? null,
                'turno_preferido' => $data['turno_preferido'] ?? null,
                'motivo' => $data['motivo'],
                'observaciones' => $data['observaciones'] ?? null,
                'medico_id' => $data['medico_id'],
                'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
            ]);

            $pivotData = [];
            if (! empty($data['observaciones_paciente'])) {
                $pivotData['observaciones'] = $data['observaciones_paciente'];
            }

            $appointment->patients()->attach($patientId, $pivotData);

            return $appointment;
        });

        $appointment->load(['doctor', 'patients', 'status']);

        activity('citas')
            ->performedOn($appointment)
            ->causedBy($user)
            ->withProperties([
                'folio' => $appointment->folio,
                'paciente_id' => $patientId,
                'medico_id' => $appointment->medico_id,
                'fecha_preferida' => $appointment->fecha_preferida,
                'turno_preferido' => $appointment->turno_preferido,
                'motivo' => $appointment->motivo,
            ])
            ->log('Solicitud de cita médica registrada');

        return $appointment;
    }
}
