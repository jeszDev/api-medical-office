<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * Determine whether the user can view any appointments.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('appointments.view');
    }

    /**
     * Determine whether the user can view the specific appointment.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        return $user->hasPermissionTo('appointments.view');
    }

    /**
     * Determine whether the user can create an appointment.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('appointments.create');
    }

    /**
     * Determine whether the user can update the appointment.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        return $user->hasPermissionTo('appointments.confirm');
    }

    /**
     * Determine whether the user can cancel the appointment.
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        return $user->hasPermissionTo('appointments.cancel');
    }

    /**
     * Determine whether the user can complete the appointment.
     */
    public function complete(User $user, Appointment $appointment): bool
    {
        return $user->hasPermissionTo('appointments.complete');
    }
}
