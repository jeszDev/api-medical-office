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
        return $user->hasPermissionTo('consultations.view');
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
        if (! $user->hasPermissionTo('consultations.update')) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $consultation->medico_id === $user->id;
    }

    /**
     * Determine whether the user can delete the consultation.
     */
    public function delete(User $user, Consultation $consultation): bool
    {
        return $user->hasPermissionTo('consultations.delete');
    }
}
