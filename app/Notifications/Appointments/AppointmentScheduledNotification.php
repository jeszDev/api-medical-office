<?php

declare(strict_types=1);

namespace App\Notifications\Appointments;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public Appointment $appointment
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        // Extension point: Return ['mail', WhatsAppChannel::class] in Phase 2
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $this->appointment->loadMissing(['doctor', 'status']);

        $clinic = (array) config('latidos.clinic', []);
        $platform = (array) config('latidos.platform', []);
        $actions = (array) config('latidos.actions', []);

        $startDate = Carbon::parse($this->appointment->fecha_hora_inicio)->locale('es');
        $endDate = Carbon::parse($this->appointment->fecha_hora_termino)->locale('es');

        $actionUrl = rtrim($actions['base_url'] ?? 'http://localhost:3000/citas', '/').'/'.$this->appointment->id;

        return (new MailMessage)
            ->subject('Confirmación de Cita Médica - '.($clinic['name'] ?? 'Clínica'))
            ->view('emails.appointments.scheduled', [
                'appointment' => $this->appointment,
                'patient' => $notifiable,
                'doctor' => $this->appointment->doctor,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'clinic' => $clinic,
                'platform' => $platform,
                'actionUrl' => $actionUrl,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'type' => 'appointment_scheduled',
            'fecha_hora_inicio' => $this->appointment->fecha_hora_inicio,
        ];
    }
}
