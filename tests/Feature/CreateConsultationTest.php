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

class CreateConsultationTest extends TestCase
{
    use RefreshDatabase;

    private User $doctor;

    private User $receptionist;

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
        $this->doctor->assignRole('doctor'); // El doctor tiene el permiso consultations.create

        $this->receptionist = User::factory()->create(['nombre' => 'Recepcionista', 'primer_apellido' => 'Josue']);
        $this->receptionist->assignRole('receptionist'); // La recepcionista NO tiene consultations.create

        $this->patient = Patient::create([
            'nombre' => 'Carlos',
            'primer_apellido' => 'Ramírez',
            'segundo_apellido' => 'Hernández',
            'fecha_nacimiento' => '1992-08-20',
            'telefono' => '5552345678',
            'correo_electronico' => 'carlos.ramirez@example.com',
        ]);

        $this->appointment = Appointment::create([
            'fecha_hora_inicio' => '2026-10-26 10:00:00',
            'fecha_hora_termino' => '2026-10-26 10:30:00',
            'motivo' => 'Dolor abdominal agudo',
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
        ]);
        $this->appointment->patients()->attach($this->patient->id);
    }

    public function test_unauthenticated_user_cannot_create_consultation(): void
    {
        $response = $this->postJson('/api/consultations', [
            'paciente_id' => $this->patient->id,
            'motivo' => 'Consulta general',
            'diagnostico' => 'Gastritis',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_without_permission_cannot_create_consultation(): void
    {
        Sanctum::actingAs($this->receptionist);

        $response = $this->postJson('/api/consultations', [
            'paciente_id' => $this->patient->id,
            'motivo' => 'Consulta general',
            'diagnostico' => 'Gastritis',
        ]);

        $response->assertStatus(403);
    }

    public function test_validation_fails_when_required_fields_are_missing(): void
    {
        Sanctum::actingAs($this->doctor);

        // Sin motivo ni diagnostico
        $response = $this->postJson('/api/consultations', [
            'paciente_id' => $this->patient->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['motivo', 'diagnostico']);
    }

    public function test_cannot_create_consultation_for_cancelled_appointment(): void
    {
        Sanctum::actingAs($this->doctor);

        // Marcar la cita previamente como cancelada
        $this->appointment->update(['cita_estatus_id' => CatalogAppointmentStatus::CANCELADA]);

        $response = $this->postJson('/api/consultations', [
            'cita_id' => $this->appointment->id,
            'paciente_id' => $this->patient->id,
            'motivo' => 'Chequeo de rutina',
            'diagnostico' => 'Sin hallazgos patológicos',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cita_id']);
    }

    public function test_doctor_can_create_consultation_and_appointment_status_updates_to_attended(): void
    {
        Sanctum::actingAs($this->doctor);

        $payload = [
            'cita_id' => $this->appointment->id,
            'paciente_id' => $this->patient->id,
            'motivo' => 'Dolor abdominal de 2 días de evolución',
            'exploracion_fisica' => 'Abdomen blando, depresible, ligero dolor en epigastrio',
            'diagnostico' => 'Gastritis aguda moderada',
            'tratamiento' => 'Omeprazol 20mg cada 24 horas por 14 días',
            'observaciones' => 'Paciente refiere mejoría previa con dieta blanda',
            'notas' => 'Cita de control en dos semanas',
            'peso' => 74.50,
            'altura' => 175.00,
            'temperatura' => 36.6,
            'presion_arterial' => '120/80',
            'frecuencia_cardiaca' => 72,
            'frecuencia_respiratoria' => 16,
        ];

        $response = $this->postJson('/api/consultations', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.diagnostico', 'Gastritis aguda moderada')
            ->assertJsonPath('data.observaciones', 'Paciente refiere mejoría previa con dieta blanda')
            ->assertJsonPath('data.medico_id', $this->doctor->id)
            ->assertJsonPath('data.paciente_id', $this->patient->id)
            ->assertJsonPath('data.cita_id', $this->appointment->id)
            ->assertJsonPath('data.signos_vitales.peso', '74.50')
            ->assertJsonPath('data.signos_vitales.presion_arterial', '120/80');

        // 1. Verificar persistencia de la consulta en base de datos
        $this->assertDatabaseHas('consultas', [
            'medico_id' => $this->doctor->id,
            'paciente_id' => $this->patient->id,
            'cita_id' => $this->appointment->id,
            'diagnostico' => 'Gastritis aguda moderada',
        ]);

        // 2. Verificar que la cita médica cambió automáticamente su estado a ATENDIDA
        $this->assertDatabaseHas('citas', [
            'id' => $this->appointment->id,
            'cita_estatus_id' => CatalogAppointmentStatus::ATENDIDA,
        ]);

        // 3. Verificar auditoría registrada con Spatie Activitylog
        $latestActivity = Activity::where('log_name', 'consultas')->latest()->first();
        $this->assertNotNull($latestActivity);
        $this->assertEquals('Consulta médica registrada', $latestActivity->description);
        $this->assertEquals($this->doctor->id, $latestActivity->causer_id);
    }
}
