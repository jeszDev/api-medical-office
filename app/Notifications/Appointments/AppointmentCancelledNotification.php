<?php

declare(strict_types=1);

namespace App\Notifications\Appointments;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public Appointment $appointment,
        public ?string $reason = null
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

        $rescheduleUrl = rtrim($actions['base_url'] ?? 'http://localhost:3000/citas', '/').($actions['reschedule_path'] ?? '/reagendar').'?id='.$this->appointment->id;

        return (new MailMessage)
            ->subject('Cancelación de Cita Médica - '.($clinic['name'] ?? 'Clínica'))
            ->view('emails.appointments.cancelled', [
                'appointment' => $this->appointment,
                'patient' => $notifiable,
                'doctor' => $this->appointment->doctor,
                'startDate' => $startDate,
                'reason' => $this->reason ?? 'Cancelada por solicitud de la clínica o paciente.',
                'clinic' => $clinic,
                'platform' => $platform,
                'rescheduleUrl' => $rescheduleUrl,
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
            'type' => 'appointment_cancelled',
            'reason' => $this->reason,
            'fecha_hora_inicio' => $this->appointment->fecha_hora_inicio,
        ];
    }
}
