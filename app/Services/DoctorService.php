<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DoctorService
{
    /**
     * Obtiene el perfil médico del usuario con sus especialidades cargadas.
     *
     * @throws ModelNotFoundException
     */
    public function getProfile(User $user): Doctor
    {
        return $user->doctor()
            ->with(['specialties'])
            ->firstOrFail();
    }

    /**
     * Actualiza la información del perfil médico y registra la auditoría.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data): Doctor
    {
        $doctor = $this->getProfile($user);

        $doctor->update($data);

        activity('medicos')
            ->performedOn($doctor)
            ->causedBy($user)
            ->withProperties(['cambios' => array_keys($data)])
            ->log('Perfil médico actualizado');

        return $doctor->load('specialties');
    }
}

