<?php

declare(strict_types=1);

namespace App\Listeners\Appointments;

use App\Events\Appointments\AppointmentCancelled;
use App\Notifications\Appointments\AppointmentCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAppointmentCancelledNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(AppointmentCancelled $event): void
    {
        $appointment = $event->appointment;
        $appointment->loadMissing('patients');

        foreach ($appointment->patients as $patient) {
            $patient->notify(new AppointmentCancelledNotification($appointment, $event->reason));
        }
    }
}
