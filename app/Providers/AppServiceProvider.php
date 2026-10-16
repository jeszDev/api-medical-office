<?php

namespace App\Providers;

use App\Events\Appointments\AppointmentCancelled;
use App\Events\Appointments\AppointmentScheduled;
use App\Listeners\Appointments\SendAppointmentCancelledNotification;
use App\Listeners\Appointments\SendAppointmentScheduledNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            AppointmentScheduled::class,
            SendAppointmentScheduledNotification::class,
        );

        Event::listen(
            AppointmentCancelled::class,
            SendAppointmentCancelledNotification::class,
        );
    }
}
