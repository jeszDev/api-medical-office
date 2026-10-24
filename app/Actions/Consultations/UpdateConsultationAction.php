<?php

declare(strict_types=1);

namespace App\Actions\Consultations;

use App\Models\Consultation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateConsultationAction
{
    /**
     * Actualiza la información clínica de una consulta médica existente y registra auditoría.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(Consultation $consultation, array $data, ?User $user = null): Consultation
    {
        $consultation = DB::transaction(function () use ($consultation, $data) {
            $consultation->update($data);

            return $consultation;
        });

        activity('consultas')
            ->performedOn($consultation)
            ->causedBy($user)
            ->withProperties([
                'paciente_id' => $consultation->paciente_id,
                'cita_id' => $consultation->cita_id,
                'diagnostico' => $consultation->diagnostico,
            ])
            ->log('Consulta médica actualizada');

        $consultation->load(['doctor', 'patient', 'appointment.status']);

        return $consultation;
    }
}
