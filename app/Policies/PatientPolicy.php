<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * Determine whether the user can view any patients.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasAnyRole(['admin', 'doctor', 'receptionist'])) {
            return $user->hasPermissionTo('patients.view');
        }

        // Paciente/Tutor puede listar sus propios expedientes vinculados
        return $user->hasRole('patient') && $user->hasPermissionTo('patients.view');
    }

    /**
     * Determine whether the user can view the specific patient.
     */
    public function view(User $user, Patient $patient): bool
    {
        // Personal de la clínica con permiso
        if ($user->hasAnyRole(['admin', 'doctor', 'receptionist'])) {
            return $user->hasPermissionTo('patients.view');
        }

        // Paciente/Tutor: Solo si el paciente está en su lista de familiares
        if ($user->hasRole('patient')) {
            return $user->patients()->where('pacientes.id', $patient->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create patients.
     */
    public function create(User $user): bool
    {
        if ($user->hasAnyRole(['admin', 'receptionist'])) {
            return $user->hasPermissionTo('patients.create');
        }

        // Pacientes/Tutores pueden dar de alta a sus familiares dependientes
        return $user->hasRole('patient') && $user->hasPermissionTo('patients.create');
    }

    /**
     * Determine whether the user can update the patient.
     */
    public function update(User $user, Patient $patient): bool
    {
        if ($user->hasAnyRole(['admin', 'receptionist'])) {
            return $user->hasPermissionTo('patients.update');
        }

        // Paciente/Tutor solo puede actualizar datos de sus familiares vinculados
        if ($user->hasRole('patient')) {
            return $user->patients()->where('pacientes.id', $patient->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the patient.
     */
    public function delete(User $user, Patient $patient): bool
    {
        return $user->hasPermissionTo('patients.delete');
    }

    /**
     * Determina si el usuario puede gestionar los tutores de un paciente.
     */
    public function manageGuardians(User $user, Patient $patient): bool
    {
        if ($user->hasAnyRole(['admin', 'receptionist'])) {
            return $user->hasPermissionTo('patients.update');
        }

        return $user->patients()
            ->where('pacientes.id', $patient->id)
            ->wherePivot('es_contacto_principal', true)
            ->exists();
    }
}
