<?php

declare(strict_types=1);

namespace App\Actions\Patients;

use App\Enums\RelationshipType;
use App\Models\Patient;
use App\Models\User;

class AttachPatientToUserAction
{
    /**
     * Vincula un paciente a un usuario con un tipo de parentesco y genera auditoría.
     */
    public function execute(
        User $user,
        Patient $patient,
        RelationshipType $relationship,
        bool $isPrimary = false,
        ?User $causer = null
    ): void {
        $user->patients()->syncWithoutDetaching([
            $patient->id => [
                'relacion' => $relationship->value,
                'es_contacto_principal' => $isPrimary,
            ],
        ]);

        activity('pacientes')
            ->performedOn($patient)
            ->causedBy($causer ?? $user)
            ->withProperties([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'relacion' => $relationship->value,
                'relacion_texto' => $relationship->label(),
                'es_contacto_principal' => $isPrimary,
            ])
            ->log('Tutor o familiar vinculado a paciente');
    }
}
