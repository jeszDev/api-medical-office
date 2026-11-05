<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RelationshipType;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Actualiza el perfil del usuario autenticado y sincroniza su expediente clínico titular si existe.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $user->update($data);

            activity('users')
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties(['campos' => array_keys($data)])
                ->log('Perfil de usuario actualizado');

            // Sincronización inversa: Si el usuario tiene expediente propio como titular, replicar cambios
            $titularPatient = $user->patients()
                ->wherePivot('relacion', RelationshipType::TITULAR->value)
                ->first();

            if ($titularPatient) {
                $patientSyncData = [];

                foreach (['nombre', 'primer_apellido', 'segundo_apellido'] as $field) {
                    if (array_key_exists($field, $data)) {
                        $patientSyncData[$field] = $data[$field];
                    }
                }

                if (array_key_exists('curp', $data)) {
                    $newCurp = $data['curp'];
                    if (!empty($newCurp)) {
                        $curpTaken = Patient::where('curp', $newCurp)
                            ->where('id', '!=', $titularPatient->id)
                            ->exists();

                        if (!$curpTaken) {
                            $patientSyncData['curp'] = $newCurp;
                        }
                    } else {
                        $patientSyncData['curp'] = null;
                    }
                }

                if (!empty($data['email']) && $data['email'] !== $titularPatient->correo_electronico) {
                    $patientSyncData['correo_electronico'] = $data['email'];
                }

                if (!empty($patientSyncData)) {
                    $titularPatient->update($patientSyncData);

                    activity('pacientes')
                        ->performedOn($titularPatient)
                        ->causedBy($user)
                        ->withProperties([
                            'motivo' => 'Sincronización automática desde perfil de usuario titular',
                            'campos' => array_keys($patientSyncData),
                        ])
                        ->log('Expediente de paciente sincronizado');
                }
            }

            return $user->loadMissing(['doctor.specialties', 'patients']);
        });
    }
}

