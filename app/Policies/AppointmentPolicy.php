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
        if ($user->hasAnyRole(['admin', 'receptionist'])) {
            return $user->hasPermissionTo('appointments.view');
        }

        if ($user->hasRole('doctor')) {
            return $user->hasPermissionTo('appointments.view') && $appointment->medico_id === $user->id;
        }

        if ($user->hasRole('patient')) {
            return $user->hasPermissionTo('appointments.view')
                && $appointment->patients()
                    ->whereIn('pacientes.id', $user->patients()->pluck('pacientes.id'))
                    ->exists();
        }

        return false;
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
        return $user->hasPermissionTo('appointments.update');
    }

    /**
     * Determine whether the user can register check-in (waiting room) for the appointment.
     */
    public function checkIn(User $user, Appointment $appointment): bool
    {
        return $user->hasPermissionTo('appointments.update');
    }

    /**
     * Determine whether the user can mark the appointment as no-show.
     */
    public function noShow(User $user, Appointment $appointment): bool
    {
        return $user->hasPermissionTo('appointments.update');
    }

    /**
     * Determine whether the user can confirm the appointment.
     */
    public function confirm(User $user, Appointment $appointment): bool
    {
        return $user->hasPermissionTo('appointments.confirm');
    }

    /**
     * Determine whether the user can cancel the appointment.
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        if ($user->hasAnyRole(['admin', 'receptionist'])) {
            return $user->hasPermissionTo('appointments.cancel');
        }

        if ($user->hasRole('patient')) {
            return $user->hasPermissionTo('appointments.cancel')
                && $appointment->patients()
                    ->whereIn('pacientes.id', $user->patients()->pluck('pacientes.id'))
                    ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can complete the appointment.
     */
    public function complete(User $user, Appointment $appointment): bool
    {
        return $user->hasPermissionTo('appointments.complete');
    }

    /**
     * Determine whether the user can delete the appointment.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->hasPermissionTo('appointments.delete');
    }
}
