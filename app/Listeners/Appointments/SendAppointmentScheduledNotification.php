<?php

declare(strict_types=1);

namespace App\Listeners\Appointments;

use App\Events\Appointments\AppointmentScheduled;
use App\Notifications\Appointments\AppointmentScheduledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAppointmentScheduledNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(AppointmentScheduled $event): void
    {
        $appointment = $event->appointment;
        $appointment->loadMissing('patients');

        foreach ($appointment->patients as $patient) {
            $patient->notify(new AppointmentScheduledNotification($appointment));
        }
    }
}
