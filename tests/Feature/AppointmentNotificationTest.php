<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\Appointments\AppointmentCancelledNotification;
use App\Notifications\Appointments\AppointmentReminderNotification;
use App\Notifications\Appointments\AppointmentScheduledNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AppointmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $receptionist;

    private User $doctor;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::PENDIENTE, 'nombre' => 'Pendiente']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CONFIRMADA, 'nombre' => 'Confirmada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CANCELADA, 'nombre' => 'Cancelada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::ATENDIDA, 'nombre' => 'Atendida']);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->doctor = User::factory()->create(['name' => 'Dr. Fernando Valenzuela']);
        $this->doctor->assignRole('doctor');

        $this->receptionist = User::factory()->create(['name' => 'Recepcionista Central']);
        $this->receptionist->assignRole('receptionist');

        $this->patient = Patient::create([
            'nombre' => 'María',
            'primer_apellido' => 'Hernández',
            'segundo_apellido' => 'López',
            'fecha_nacimiento' => '1992-04-10',
            'telefono' => '5559876543',
            'correo_electronico' => 'maria.hernandez@example.com',
        ]);
    }

    public function test_scheduling_appointment_dispatches_event_and_notifies_patient(): void
    {
        Notification::fake();

        Sanctum::actingAs($this->receptionist);

        $payload = [
            'fecha_hora_inicio' => '2026-11-10 10:00:00',
            'fecha_hora_termino' => '2026-11-10 10:45:00',
            'motivo' => 'Consulta de Chequeo Anual',
            'medico_id' => $this->doctor->id,
            'paciente_id' => $this->patient->id,
        ];

        $response = $this->postJson('/api/appointments', $payload);
        $response->assertStatus(201);

        Notification::assertSentTo(
            $this->patient,
            AppointmentScheduledNotification::class,
            function (AppointmentScheduledNotification $notification) {
                return $notification->appointment->motivo === 'Consulta de Chequeo Anual';
            }
        );
    }

    public function test_cancelling_appointment_dispatches_event_and_notifies_patient_with_reason(): void
    {
        Notification::fake();

        Sanctum::actingAs($this->receptionist);

        $appointment = Appointment::create([
            'fecha_hora_inicio' => '2026-11-12 11:00:00',
            'fecha_hora_termino' => '2026-11-12 11:30:00',
            'motivo' => 'Revisión general',
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
        ]);
        $appointment->patients()->attach($this->patient->id);

        $response = $this->patchJson("/api/appointments/{$appointment->id}/cancel", [
            'motivo_cancelacion' => 'El médico tiene una reunión académica urgente.',
        ]);

        $response->assertStatus(200);

        Notification::assertSentTo(
            $this->patient,
            AppointmentCancelledNotification::class,
            function (AppointmentCancelledNotification $notification) {
                return $notification->reason === 'El médico tiene una reunión académica urgente.';
            }
        );
    }

    public function test_send_appointment_reminders_command_notifies_patients_with_appointments_tomorrow(): void
    {
        Notification::fake();

        $tomorrow = now()->addDay();

        // Appointment scheduled for tomorrow
        $appointmentTomorrow = Appointment::create([
            'fecha_hora_inicio' => $tomorrow->copy()->setHour(9)->setMinute(0)->format('Y-m-d H:i:s'),
            'fecha_hora_termino' => $tomorrow->copy()->setHour(9)->setMinute(30)->format('Y-m-d H:i:s'),
            'motivo' => 'Cita de seguimiento mañana',
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::CONFIRMADA,
        ]);
        $appointmentTomorrow->patients()->attach($this->patient->id);

        // Appointment scheduled for 5 days later (should NOT receive reminder today)
        $appointmentFuture = Appointment::create([
            'fecha_hora_inicio' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'fecha_hora_termino' => now()->addDays(5)->addMinutes(30)->format('Y-m-d H:i:s'),
            'motivo' => 'Cita lejana',
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::CONFIRMADA,
        ]);
        $appointmentFuture->patients()->attach($this->patient->id);

        $this->artisan('appointments:send-reminders')
            ->assertSuccessful();

        Notification::assertSentTo(
            $this->patient,
            AppointmentReminderNotification::class,
            function (AppointmentReminderNotification $notification) use ($appointmentTomorrow) {
                return $notification->appointment->id === $appointmentTomorrow->id;
            }
        );

        Notification::assertNotSentTo(
            $this->patient,
            AppointmentReminderNotification::class,
            function (AppointmentReminderNotification $notification) use ($appointmentFuture) {
                return $notification->appointment->id === $appointmentFuture->id;
            }
        );
    }

    public function test_email_preview_routes_render_html_successfully(): void
    {
        $previewTypes = ['scheduled', 'cancelled', 'reminder'];

        foreach ($previewTypes as $type) {
            $response = $this->get("/dev/email-preview/{$type}");

            $response->assertStatus(200);
            $response->assertSee('Hospital Star Médica');
            $response->assertSee('Latidos');
            $response->assertSee('Ana Laura');
        }

        // Verify 3 specific action buttons in reminder template
        $reminderResponse = $this->get('/dev/email-preview/reminder');
        $reminderResponse->assertSee('Confirmar mi Asistencia');
        $reminderResponse->assertSee('Posponer / Reagendar');
        $reminderResponse->assertSee('No podré asistir (Cancelar)');
    }
}
