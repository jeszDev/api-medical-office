<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\CatalogAppointmentStatusSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    private User $receptionist;
    private User $userWithoutPermissions;
    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolesAndPermissionsSeeder::class,
            CatalogAppointmentStatusSeeder::class,
        ]);

        $this->receptionist = User::factory()->create(['name' => 'Recepcionista Maria']);
        $this->receptionist->assignRole('receptionist'); // Tiene patients.view, patients.create, patients.update

        $this->userWithoutPermissions = User::factory()->create(['name' => 'Usuario Sin Roles']);

        $this->patient = Patient::create([
            'nombre' => 'Roberto',
            'primer_apellido' => 'Gómez',
            'segundo_apellido' => 'Bolaños',
            'curp' => 'GOMR800101HDFR0001',
            'fecha_nacimiento' => '1980-01-01',
            'telefono' => '5551122334',
            'correo_electronico' => 'roberto.gomez@example.com',
            'observaciones' => 'Sin alergias',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_patients(): void
    {
        $response = $this->getJson('/api/patients');
        $response->assertStatus(401);
    }

    public function test_user_without_permission_cannot_access_patients(): void
    {
        Sanctum::actingAs($this->userWithoutPermissions);

        $response = $this->getJson('/api/patients');
        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_list_and_search_patients(): void
    {
        Sanctum::actingAs($this->receptionist);

        // Crear un segundo paciente para probar búsqueda
        Patient::create([
            'nombre' => 'Elena',
            'primer_apellido' => 'Torres',
            'curp' => 'TORE850202MDFR0002',
        ]);

        $response = $this->getJson('/api/patients?search=Roberto Gómez');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Roberto')
            ->assertJsonPath('data.0.primer_apellido', 'Gómez');
    }

    public function test_user_can_list_all_patients_without_pagination_when_page_is_zero(): void
    {
        Sanctum::actingAs($this->receptionist);

        $response = $this->getJson('/api/patients?page=0');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'nombre', 'primer_apellido']]]);
    }

    public function test_store_patient_validation_fails_on_empty_data(): void
    {
        Sanctum::actingAs($this->receptionist);

        $response = $this->postJson('/api/patients', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre', 'primer_apellido']);
    }

    public function test_store_patient_successfully(): void
    {
        Sanctum::actingAs($this->receptionist);

        $payload = [
            'nombre' => 'Lucía',
            'primer_apellido' => 'Méndez',
            'segundo_apellido' => 'Castro',
            'curp' => 'MENL900505MDFR0003',
            'fecha_nacimiento' => '1990-05-05',
            'telefono' => '5559988776',
            'correo_electronico' => 'lucia.mendez@example.com',
            'observaciones' => 'Hipertensión controlada',
        ];

        $response = $this->postJson('/api/patients', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.nombre', 'Lucía')
            ->assertJsonPath('data.primer_apellido', 'Méndez')
            ->assertJsonPath('data.nombre_completo', 'Lucía Méndez Castro');

        $this->assertDatabaseHas('pacientes', [
            'curp' => 'MENL900505MDFR0003',
            'nombre' => 'Lucía',
        ]);

        $latestActivity = Activity::where('log_name', 'pacientes')->latest()->first();
        $this->assertNotNull($latestActivity);
        $this->assertEquals('Paciente registrado', $latestActivity->description);
    }

    public function test_show_patient_returns_resource(): void
    {
        Sanctum::actingAs($this->receptionist);

        $response = $this->getJson("/api/patients/{$this->patient->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $this->patient->id)
            ->assertJsonPath('data.nombre', 'Roberto')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nombre',
                    'primer_apellido',
                    'citas',
                    'consultas',
                ],
            ])
            ->assertJsonCount(0, 'data.citas')
            ->assertJsonCount(0, 'data.consultas');
    }

    public function test_show_patient_returns_appointments_and_consultations_history(): void
    {
        Sanctum::actingAs($this->receptionist);

        $doctor = User::factory()->create(['name' => 'Dr. House']);

        // Crear una cita vinculada al paciente
        $appointment = Appointment::create([
            'fecha_hora_inicio' => '2026-10-25 10:00:00',
            'fecha_hora_termino' => '2026-10-25 10:30:00',
            'motivo' => 'Revisión anual',
            'medico_id' => $doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::ATENDIDA,
        ]);
        $appointment->patients()->attach($this->patient->id, ['observaciones' => 'Paciente puntual']);

        // Crear una consulta médica ligada a la cita
        $consultationWithAppointment = Consultation::create([
            'cita_id' => $appointment->id,
            'paciente_id' => $this->patient->id,
            'medico_id' => $doctor->id,
            'motivo' => 'Revisión general',
            'diagnostico' => 'Saludable',
        ]);

        // Crear una consulta médica sin cita (consulta directa/urgencia)
        $directConsultation = Consultation::create([
            'cita_id' => null,
            'paciente_id' => $this->patient->id,
            'medico_id' => $doctor->id,
            'motivo' => 'Urgencia menor',
            'diagnostico' => 'Cefalea tensional',
        ]);

        $response = $this->getJson("/api/patients/{$this->patient->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $this->patient->id)
            ->assertJsonCount(1, 'data.citas')
            ->assertJsonPath('data.citas.0.id', $appointment->id)
            ->assertJsonPath('data.citas.0.motivo', 'Revisión anual')
            ->assertJsonPath('data.citas.0.consulta.id', $consultationWithAppointment->id)
            ->assertJsonPath('data.citas.0.consulta.diagnostico', 'Saludable')
            ->assertJsonCount(2, 'data.consultas')
            ->assertJsonPath('data.consultas.0.id', $directConsultation->id)
            ->assertJsonPath('data.consultas.1.id', $consultationWithAppointment->id);
    }

    public function test_update_patient_successfully(): void
    {
        Sanctum::actingAs($this->receptionist);

        $response = $this->putJson("/api/patients/{$this->patient->id}", [
            'nombre' => 'Roberto Carlos',
            'primer_apellido' => 'Gómez',
            'telefono' => '5550001111',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.nombre', 'Roberto Carlos')
            ->assertJsonPath('data.telefono', '5550001111');

        $this->assertDatabaseHas('pacientes', [
            'id' => $this->patient->id,
            'nombre' => 'Roberto Carlos',
            'telefono' => '5550001111',
        ]);

        $latestActivity = Activity::where('log_name', 'pacientes')->latest()->first();
        $this->assertNotNull($latestActivity);
        $this->assertEquals('Paciente actualizado', $latestActivity->description);
    }

    public function test_destroy_patient_successfully(): void
    {
        // En el seeder, el rol admin o permiso patients.delete
        // Asignemos el permiso patients.delete a la recepcionista para este test
        $this->receptionist->givePermissionTo('patients.delete');
        Sanctum::actingAs($this->receptionist);

        $response = $this->deleteJson("/api/patients/{$this->patient->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('pacientes', [
            'id' => $this->patient->id,
        ]);

        $latestActivity = Activity::where('log_name', 'pacientes')->latest()->first();
        $this->assertNotNull($latestActivity);
        $this->assertEquals('Paciente eliminado', $latestActivity->description);
    }
}
