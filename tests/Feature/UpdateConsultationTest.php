<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class UpdateConsultationTest extends TestCase
{
    use RefreshDatabase;

    private User $doctor;

    private User $receptionist;

    private Patient $patient;

    private Consultation $consultation;

    protected function setUp(): void
    {
        parent::setUp();

        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::PENDIENTE, 'nombre' => 'Pendiente']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CONFIRMADA, 'nombre' => 'Confirmada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CANCELADA, 'nombre' => 'Cancelada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::ATENDIDA, 'nombre' => 'Atendida']);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->doctor = User::factory()->create(['nombre' => 'Gregory', 'primer_apellido' => 'House']);
        $this->doctor->assignRole('doctor'); // El doctor tiene el permiso consultations.update

        $this->receptionist = User::factory()->create(['nombre' => 'Recepcionista', 'primer_apellido' => 'Josue']);
        $this->receptionist->assignRole('receptionist'); // La recepcionista NO tiene consultations.update

        $this->patient = Patient::create([
            'nombre' => 'Carlos',
            'primer_apellido' => 'Ramírez',
            'segundo_apellido' => 'Hernández',
            'fecha_nacimiento' => '1992-08-20',
            'telefono' => '5552345678',
            'correo_electronico' => 'carlos.ramirez@example.com',
        ]);

        $this->consultation = Consultation::create([
            'paciente_id' => $this->patient->id,
            'medico_id' => $this->doctor->id,
            'motivo' => 'Dolor de cabeza recurrente',
            'diagnostico' => 'Cefalea tensional',
            'tratamiento' => 'Paracetamol 500mg',
            'peso' => 70.00,
            'altura' => 172.00,
            'temperatura' => 36.5,
            'presion_arterial' => '120/80',
            'frecuencia_cardiaca' => 75,
            'frecuencia_respiratoria' => 18,
        ]);
    }

    public function test_unauthenticated_user_cannot_update_consultation(): void
    {
        $response = $this->putJson("/api/consultations/{$this->consultation->id}", [
            'diagnostico' => 'Migraña sin aura',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_without_permission_cannot_update_consultation(): void
    {
        Sanctum::actingAs($this->receptionist);

        $response = $this->putJson("/api/consultations/{$this->consultation->id}", [
            'diagnostico' => 'Migraña sin aura',
        ]);

        $response->assertStatus(403);
    }

    public function test_validation_fails_when_invalid_data_provided(): void
    {
        Sanctum::actingAs($this->doctor);

        // Temperatura fuera de rango permisible (30 a 45) y peso negativo
        $response = $this->putJson("/api/consultations/{$this->consultation->id}", [
            'temperatura' => 55.0,
            'peso' => -5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['temperatura', 'peso']);
    }

    public function test_doctor_can_update_consultation_successfully(): void
    {
        Sanctum::actingAs($this->doctor);

        $payload = [
            'motivo' => 'Cefalea intensa con fotofobia',
            'exploracion_fisica' => 'Fondo de ojo normal, reflejos osteotendinosos conservados',
            'diagnostico' => 'Migraña clásica',
            'tratamiento' => 'Sumatriptán 50mg al inicio del dolor',
            'observaciones' => 'Evitar alimentos desencadenantes',
            'notas' => 'Control en 1 mes si persisten crisis',
            'peso' => 71.50,
            'altura' => 172.00,
            'temperatura' => 36.7,
            'presion_arterial' => '118/78',
            'frecuencia_cardiaca' => 72,
            'frecuencia_respiratoria' => 16,
        ];

        $response = $this->putJson("/api/consultations/{$this->consultation->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $this->consultation->id)
            ->assertJsonPath('data.motivo', 'Cefalea intensa con fotofobia')
            ->assertJsonPath('data.diagnostico', 'Migraña clásica')
            ->assertJsonPath('data.tratamiento', 'Sumatriptán 50mg al inicio del dolor')
            ->assertJsonPath('data.signos_vitales.peso', '71.50')
            ->assertJsonPath('data.signos_vitales.presion_arterial', '118/78');

        // Verificar base de datos
        $this->assertDatabaseHas('consultas', [
            'id' => $this->consultation->id,
            'diagnostico' => 'Migraña clásica',
            'tratamiento' => 'Sumatriptán 50mg al inicio del dolor',
        ]);

        // Verificar auditoría con Spatie Activitylog
        $latestActivity = Activity::where('log_name', 'consultas')->latest()->first();
        $this->assertNotNull($latestActivity);
        $this->assertEquals('Consulta médica actualizada', $latestActivity->description);
        $this->assertEquals($this->doctor->id, $latestActivity->causer_id);
    }

    public function test_partial_update_via_patch_works(): void
    {
        Sanctum::actingAs($this->doctor);

        $response = $this->patchJson("/api/consultations/{$this->consultation->id}", [
            'diagnostico' => 'Cefalea mixta',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.diagnostico', 'Cefalea mixta')
            ->assertJsonPath('data.motivo', 'Dolor de cabeza recurrente'); // Mantiene el motivo anterior

        $this->assertDatabaseHas('consultas', [
            'id' => $this->consultation->id,
            'diagnostico' => 'Cefalea mixta',
            'motivo' => 'Dolor de cabeza recurrente',
        ]);
    }

    public function test_doctor_cannot_update_consultation_created_by_another_doctor(): void
    {
        $anotherDoctor = User::factory()->create(['nombre' => 'James', 'primer_apellido' => 'Wilson']);
        $anotherDoctor->assignRole('doctor');

        Sanctum::actingAs($anotherDoctor);

        $response = $this->putJson("/api/consultations/{$this->consultation->id}", [
            'diagnostico' => 'Diagnóstico no autorizado',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_any_consultation(): void
    {
        $admin = User::factory()->create(['nombre' => 'Super', 'primer_apellido' => 'Admin']);
        $admin->assignRole('admin');

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/consultations/{$this->consultation->id}", [
            'observaciones' => 'Revisión por auditoría médica',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.observaciones', 'Revisión por auditoría médica');
    }
}
