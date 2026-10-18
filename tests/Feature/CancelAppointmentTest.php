<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class CancelAppointmentTest extends TestCase
{
    use RefreshDatabase;

    private User $receptionist;
    private User $doctor;
    private Patient $patient;
    private Appointment $appointment;

    protected function setUp(): void
    {
        parent::setUp();

        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::PENDIENTE, 'nombre' => 'Pendiente']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CONFIRMADA, 'nombre' => 'Confirmada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CANCELADA, 'nombre' => 'Cancelada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::ATENDIDA, 'nombre' => 'Atendida']);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->doctor = User::factory()->create(['nombre' => 'Gregory', 'primer_apellido' => 'House']);
        $this->doctor->assignRole('doctor'); // En el seeder el doctor NO tiene appointments.cancel

        $this->receptionist = User::factory()->create(['nombre' => 'Recepcionista', 'primer_apellido' => 'General']);
        $this->receptionist->assignRole('receptionist'); // Recepcionista SÍ tiene appointments.cancel

        $this->patient = Patient::create([
            'nombre' => 'Ana',
            'primer_apellido' => 'López',
            'segundo_apellido' => 'Martínez',
            'fecha_nacimiento' => '1985-02-10',
            'telefono' => '5559876543',
            'correo_electronico' => 'ana.lopez@example.com',
        ]);

        $this->appointment = Appointment::create([
            'fecha_hora_inicio' => '2026-10-25 10:00:00',
            'fecha_hora_termino' => '2026-10-25 10:30:00',
            'motivo' => 'Revisión periódica',
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
        ]);
        $this->appointment->patients()->attach($this->patient->id);
    }

    public function test_unauthenticated_user_cannot_cancel_appointment(): void
    {
        $response = $this->patchJson("/api/appointments/{$this->appointment->id}/cancel", [
            'motivo_cancelacion' => 'El paciente canceló por teléfono',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_without_permission_cannot_cancel_appointment(): void
    {
        Sanctum::actingAs($this->doctor);

        $response = $this->patchJson("/api/appointments/{$this->appointment->id}/cancel", [
            'motivo_cancelacion' => 'El médico no puede cancelar según roles',
        ]);

        $response->assertStatus(403);
    }

    public function test_cancellation_requires_a_reason(): void
    {
        Sanctum::actingAs($this->receptionist);

        $response = $this->patchJson("/api/appointments/{$this->appointment->id}/cancel", [
            'motivo_cancelacion' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['motivo_cancelacion']);
    }

    public function test_cannot_cancel_already_cancelled_appointment(): void
    {
        Sanctum::actingAs($this->receptionist);

        // Marcar la cita previamente como cancelada
        $this->appointment->update(['cita_estatus_id' => CatalogAppointmentStatus::CANCELADA]);

        $response = $this->patchJson("/api/appointments/{$this->appointment->id}/cancel", [
            'motivo_cancelacion' => 'Intento cancelar de nuevo',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['appointment']);
    }

    public function test_cannot_cancel_already_attended_appointment(): void
    {
        Sanctum::actingAs($this->receptionist);

        // Marcar la cita como atendida
        $this->appointment->update(['cita_estatus_id' => CatalogAppointmentStatus::ATENDIDA]);

        $response = $this->patchJson("/api/appointments/{$this->appointment->id}/cancel", [
            'motivo_cancelacion' => 'Cita ya realizada no se puede cancelar',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['appointment']);
    }

    public function test_authorized_user_can_cancel_appointment_and_it_records_audit_log(): void
    {
        Sanctum::actingAs($this->receptionist);

        $reason = 'El paciente tuvo una emergencia de viaje y solicitó cancelar';

        $response = $this->patchJson("/api/appointments/{$this->appointment->id}/cancel", [
            'motivo_cancelacion' => $reason,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $this->appointment->id)
            ->assertJsonPath('data.estatus.nombre', 'Cancelada')
            ->assertJsonPath('data.estatus.id', CatalogAppointmentStatus::CANCELADA);

        // 1. Verificar persistencia en base de datos
        $this->assertDatabaseHas('citas', [
            'id' => $this->appointment->id,
            'cita_estatus_id' => CatalogAppointmentStatus::CANCELADA,
        ]);

        // 2. Verificar registro en tabla de auditoría (Activitylog)
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'citas',
            'description' => 'Cita médica cancelada',
            'subject_type' => Appointment::class,
            'subject_id' => $this->appointment->id,
            'causer_type' => User::class,
            'causer_id' => $this->receptionist->id,
        ]);

        // 3. Verificar que las propiedades guardadas en el log contienen el motivo
        $latestActivity = Activity::forSubject($this->appointment)->latest()->first();
        $this->assertNotNull($latestActivity);
        $this->assertEquals($reason, $latestActivity->getExtraProperty('motivo_cancelacion'));
        $this->assertEquals('Pendiente', $latestActivity->getExtraProperty('estatus_anterior'));
        $this->assertEquals('Cancelada', $latestActivity->getExtraProperty('nuevo_estatus'));
    }
}
