<?php

declare(strict_types=1);

namespace App\Actions\Consultations;

use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\Consultation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateConsultationAction
{
    /**
     * Ejecuta el proceso de negocio para registrar una consulta médica,
     * actualizar el estado de la cita si existe, y registrar la auditoría.
     *
     * @param  array<string, mixed>  $data
     * @throws ValidationException
     */
    public function execute(array $data, User $doctor): Consultation
    {
        // 1. Validaciones de negocio previas
        if (!empty($data['cita_id'])) {
            $appointment = Appointment::find($data['cita_id']);
            if ($appointment && $appointment->cita_estatus_id === CatalogAppointmentStatus::CANCELADA) {
                throw ValidationException::withMessages([
                    'cita_id' => ['No es posible registrar una consulta médica sobre una cita cancelada.'],
                ]);
            }
        }

        // 2. Persistencia atómica de la consulta y actualización de la cita
        $consultation = DB::transaction(function () use ($data, $doctor) {
            $consultation = Consultation::create([
                ...$data,
                'medico_id' => $doctor->id,
            ]);

            if (!empty($data['cita_id'])) {
                $appointment = Appointment::find($data['cita_id']);
                if ($appointment) {
                    $appointment->update([
                        'cita_estatus_id' => CatalogAppointmentStatus::ATENDIDA,
                    ]);
                }
            }

            return $consultation;
        });

        // 3. Auditoría clínica con Spatie Activitylog
        activity('consultas')
            ->performedOn($consultation)
            ->causedBy($doctor)
            ->withProperties([
                'paciente_id' => $consultation->paciente_id,
                'cita_id' => $consultation->cita_id,
                'diagnostico' => $consultation->diagnostico,
            ])
            ->log('Consulta médica registrada');

        // 4. Eager loading para evitar N+1 queries al serializar
        $consultation->load(['doctor', 'patient', 'appointment.status']);

        // 5. Punto de extensión futuro (ej. generar receta en PDF o enviar por WhatsApp)
        // event(new ConsultationCompleted($consultation));

        return $consultation;
    }
}
