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
        return $user->hasPermissionTo('patients.view');
    }

    /**
     * Determine whether the user can view the specific patient.
     */
    public function view(User $user, Patient $patient): bool
    {
        return $user->hasPermissionTo('patients.view');
    }

    /**
     * Determine whether the user can create patients.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('patients.create');
    }

    /**
     * Determine whether the user can update the patient.
     */
    public function update(User $user, Patient $patient): bool
    {
        return $user->hasPermissionTo('patients.update');
    }

    /**
     * Determine whether the user can delete the patient.
     */
    public function delete(User $user, Patient $patient): bool
    {
        return $user->hasPermissionTo('patients.delete');
    }
}
