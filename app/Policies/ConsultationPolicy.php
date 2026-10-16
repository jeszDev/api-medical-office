<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Consultation;
use App\Models\User;

class ConsultationPolicy
{
    /**
     * Determine whether the user can view any consultations.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('consultations.view');
    }

    /**
     * Determine whether the user can view the specific consultation.
     */
    public function view(User $user, Consultation $consultation): bool
    {
        if ($user->hasPermissionTo('consultations.view')) {
            return true;
        }

        // Permitir al paciente o tutor familiar vinculado ver la consulta
        if ($user->hasRole('patient')) {
            return $user->patients()->where('pacientes.id', $consultation->paciente_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create consultations.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('consultations.create');
    }

    /**
     * Determine whether the user can update the consultation.
     */
    public function update(User $user, Consultation $consultation): bool
    {
        return $user->hasPermissionTo('consultations.update');
    }

    /**
     * Determine whether the user can delete the consultation.
     */
    public function delete(User $user, Consultation $consultation): bool
    {
        return $user->hasPermissionTo('consultations.delete');
    }

    /**
     * Determine whether the user can generate the prescription PDF.
     */
    public function generatePrescription(User $user, Consultation $consultation): bool
    {
        return $user->hasPermissionTo('consultations.update') || $user->id === $consultation->medico_id;
    }

    /**
     * Determine whether the user can view or download the prescription PDF.
     */
    public function viewPrescription(User $user, Consultation $consultation): bool
    {
        return $this->view($user, $consultation);
    }
}
