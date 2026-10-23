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
use Tests\TestCase;

class PatientConsultationTest extends TestCase
{
    use RefreshDatabase;

    private User $doctor;

    private User $tutor;

    private User $unrelatedPatient;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::PENDIENTE, 'nombre' => 'Pendiente']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CONFIRMADA, 'nombre' => 'Confirmada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CANCELADA, 'nombre' => 'Cancelada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::ATENDIDA, 'nombre' => 'Atendida']);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->doctor = User::factory()->create(['name' => 'Dra. Ana López']);
        $this->doctor->assignRole('doctor');

        $this->patient = Patient::create([
            'nombre' => 'Sofía',
            'primer_apellido' => 'Mendoza',
            'segundo_apellido' => 'García',
            'fecha_nacimiento' => '2018-05-12',
            'telefono' => '5551112233',
            'correo_electronico' => 'sofia.mendoza@example.com',
        ]);

        $this->tutor = User::factory()->create(['name' => 'Papá Tutor']);
        $this->tutor->assignRole('patient');
        $this->patient->guardians()->attach($this->tutor->id, [
            'relacion' => 'padre',
            'es_contacto_principal' => true,
        ]);

        $this->unrelatedPatient = User::factory()->create(['name' => 'Paciente Desconocido']);
        $this->unrelatedPatient->assignRole('patient');
    }

    public function test_doctor_can_list_all_patient_consultations_with_all_param(): void
    {
        Sanctum::actingAs($this->doctor);

        Consultation::factory()->count(3)->create([
            'paciente_id' => $this->patient->id,
            'medico_id' => $this->doctor->id,
        ]);

        $response = $this->getJson("/api/patients/{$this->patient->id}/consultations?all=true");

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'motivo',
                        'diagnostico',
                        'signos_vitales',
                        'medico',
                        'creado_el',
                    ],
                ],
            ]);
    }

    public function test_doctor_can_filter_patient_consultations_by_search_with_pagination(): void
    {
        Sanctum::actingAs($this->doctor);

        Consultation::factory()->create([
            'paciente_id' => $this->patient->id,
            'medico_id' => $this->doctor->id,
            'motivo' => 'Gastroenteritis viral aguda',
            'diagnostico' => 'Infección estomacal',
        ]);

        Consultation::factory()->create([
            'paciente_id' => $this->patient->id,
            'medico_id' => $this->doctor->id,
            'motivo' => 'Control de asma infantil',
            'diagnostico' => 'Asma leve persistente',
        ]);

        $response = $this->getJson("/api/patients/{$this->patient->id}/consultations?search=asma&per_page=5");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.motivo', 'Control de asma infantil');
    }

    public function test_doctor_can_create_consultation_directly_for_patient(): void
    {
        Sanctum::actingAs($this->doctor);

        $payload = [
            'motivo' => 'Revisión pediátrica de rutina',
            'diagnostico' => 'Paciente en estado saludable y desarrollo normal',
            'tratamiento' => 'Mantener dieta balanceada y multivitamínicos',
            'peso' => 22.5,
            'altura' => 115.0,
            'temperatura' => 36.6,
            'frecuencia_cardiaca' => 95,
        ];

        $response = $this->postJson("/api/patients/{$this->patient->id}/consultations", $payload);

        $response->assertCreated()
            ->assertJsonPath('data.paciente_id', $this->patient->id)
            ->assertJsonPath('data.motivo', 'Revisión pediátrica de rutina')
            ->assertJsonPath('data.signos_vitales.peso', '22.50');

        $this->assertDatabaseHas('consultas', [
            'paciente_id' => $this->patient->id,
            'medico_id' => $this->doctor->id,
            'motivo' => 'Revisión pediátrica de rutina',
        ]);
    }

    public function test_linked_guardian_can_view_patient_consultations(): void
    {
        Sanctum::actingAs($this->tutor);

        Consultation::factory()->create([
            'paciente_id' => $this->patient->id,
            'medico_id' => $this->doctor->id,
            'motivo' => 'Vacunación',
        ]);

        $response = $this->getJson("/api/patients/{$this->patient->id}/consultations?all=true");

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_unrelated_patient_cannot_view_patient_consultations(): void
    {
        Sanctum::actingAs($this->unrelatedPatient);

        $response = $this->getJson("/api/patients/{$this->patient->id}/consultations");

        $response->assertForbidden();
    }

    public function test_show_returns_404_if_consultation_does_not_belong_to_patient(): void
    {
        Sanctum::actingAs($this->doctor);

        $otherPatient = Patient::create([
            'nombre' => 'Lucas',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => '2020-01-01',
        ]);

        $consultation = Consultation::factory()->create([
            'paciente_id' => $otherPatient->id,
            'medico_id' => $this->doctor->id,
        ]);

        $response = $this->getJson("/api/patients/{$this->patient->id}/consultations/{$consultation->id}");

        $response->assertNotFound();
    }
}
