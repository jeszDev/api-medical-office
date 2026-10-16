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
use Tests\TestCase;

class CreateAppointmentTest extends TestCase
{
    use RefreshDatabase;

    private User $receptionist;

    private User $doctor;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        // Inicializar catálogos y roles
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::PENDIENTE, 'nombre' => 'Pendiente']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CONFIRMADA, 'nombre' => 'Confirmada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CANCELADA, 'nombre' => 'Cancelada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::ATENDIDA, 'nombre' => 'Atendida']);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->doctor = User::factory()->create(['name' => 'Dr. House']);
        $this->doctor->assignRole('doctor');

        $this->receptionist = User::factory()->create(['name' => 'Recepcionista']);
        $this->receptionist->assignRole('receptionist');

        $this->patient = Patient::create([
            'nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'segundo_apellido' => 'García',
            'fecha_nacimiento' => '1990-05-15',
            'telefono' => '5551234567',
            'correo_electronico' => 'juan.perez@example.com',
        ]);
    }

    public function test_unauthenticated_user_cannot_create_appointment(): void
    {
        $response = $this->postJson('/api/appointments', [
            'fecha_hora_inicio' => '2026-10-20 09:00:00',
            'fecha_hora_termino' => '2026-10-20 09:30:00',
            'medico_id' => $this->doctor->id,
            'paciente_id' => $this->patient->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_user_without_permission_cannot_create_appointment(): void
    {
        // El doctor en el seeder no tiene permiso appointments.create
        Sanctum::actingAs($this->doctor);

        $response = $this->postJson('/api/appointments', [
            'fecha_hora_inicio' => '2026-10-20 09:00:00',
            'fecha_hora_termino' => '2026-10-20 09:30:00',
            'medico_id' => $this->doctor->id,
            'paciente_id' => $this->patient->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_validation_fails_when_dates_are_invalid(): void
    {
        Sanctum::actingAs($this->receptionist);

        // fecha_hora_termino anterior a fecha_hora_inicio
        $response = $this->postJson('/api/appointments', [
            'fecha_hora_inicio' => '2026-10-20 10:00:00',
            'fecha_hora_termino' => '2026-10-20 09:00:00',
            'medico_id' => $this->doctor->id,
            'paciente_id' => $this->patient->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fecha_hora_termino']);
    }

    public function test_cannot_create_appointment_with_overlapping_schedule(): void
    {
        Sanctum::actingAs($this->receptionist);

        // Crear una cita previa existente
        $existing = Appointment::create([
            'fecha_hora_inicio' => '2026-10-20 09:00:00',
            'fecha_hora_termino' => '2026-10-20 10:00:00',
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
        ]);
        $existing->patients()->attach($this->patient->id);

        // Intentar crear otra cita que colisione dentro del mismo rango
        $response = $this->postJson('/api/appointments', [
            'fecha_hora_inicio' => '2026-10-20 09:30:00',
            'fecha_hora_termino' => '2026-10-20 10:30:00',
            'medico_id' => $this->doctor->id,
            'paciente_id' => $this->patient->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fecha_hora_inicio']);
    }

    public function test_authorized_user_can_create_appointment_successfully(): void
    {
        Sanctum::actingAs($this->receptionist);

        $payload = [
            'fecha_hora_inicio' => '2026-10-21 11:00:00',
            'fecha_hora_termino' => '2026-10-21 11:45:00',
            'medico_id' => $this->doctor->id,
            'paciente_id' => $this->patient->id,
            'motivo' => 'Consulta general',
            'observaciones' => 'Primera visita',
            'observaciones_paciente' => 'Sin alergias conocidas',
        ];

        $response = $this->postJson('/api/appointments', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.motivo', 'Consulta general')
            ->assertJsonPath('data.observaciones', 'Primera visita')
            ->assertJsonPath('data.medico_id', $this->doctor->id)
            ->assertJsonPath('data.cita_estatus_id', CatalogAppointmentStatus::PENDIENTE)
            ->assertJsonPath('data.pacientes.0.id', $this->patient->id);

        $folio = $response->json('data.folio');
        $this->assertNotNull($folio);
        $this->assertMatchesRegularExpression('/^CIT-\d{4}-\d{4}$/', $folio);

        $this->assertDatabaseHas('citas', [
            'medico_id' => $this->doctor->id,
            'motivo' => 'Consulta general',
            'observaciones' => 'Primera visita',
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
        ]);

        $this->assertDatabaseHas('cita_paciente', [
            'paciente_id' => $this->patient->id,
            'observaciones' => 'Sin alergias conocidas',
        ]);
    }

    public function test_folios_are_generated_consecutively_per_year(): void
    {
        $appointment1 = Appointment::create([
            'fecha_hora_inicio' => '2026-05-10 10:00:00',
            'fecha_hora_termino' => '2026-05-10 10:30:00',
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
        ]);

        $appointment2 = Appointment::create([
            'fecha_hora_inicio' => '2026-05-10 11:00:00',
            'fecha_hora_termino' => '2026-05-10 11:30:00',
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
        ]);

        $this->assertSame('CIT-2026-0001', $appointment1->folio);
        $this->assertSame('CIT-2026-0002', $appointment2->folio);
    }
}
