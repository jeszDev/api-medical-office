<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RelationshipType;
use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class PrescriptionTest extends TestCase
{
    use RefreshDatabase;

    private User $doctor;
    private User $patientUser;
    private User $unrelatedPatientUser;
    private Patient $patient;
    private Consultation $consultation;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('filesystems.default', 'local'));
        Storage::fake('public');

        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::PENDIENTE, 'nombre' => 'Pendiente']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CONFIRMADA, 'nombre' => 'Confirmada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CANCELADA, 'nombre' => 'Cancelada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::ATENDIDA, 'nombre' => 'Atendida']);

        $this->seed(RolesAndPermissionsSeeder::class);

        // Doctor con datos iniciales
        $this->doctor = User::factory()->create([
            'name' => 'Dra. Jessie Martínez',
            'email' => 'jessie@example.com',
            'cedula_profesional' => '12345678',
            'cedula_especialidad' => '87654321',
            'especialidad' => 'Pediatría',
            'institucion_egreso' => 'UNAM',
            'telefono_consultorio' => '555-123-4567',
        ]);
        $this->doctor->assignRole('doctor');

        // Paciente titular / tutor
        $this->patientUser = User::factory()->create([
            'name' => 'Carlos Tutor',
            'email' => 'carlos@example.com',
        ]);
        $this->patientUser->assignRole('patient');

        // Paciente ajeno
        $this->unrelatedPatientUser = User::factory()->create([
            'name' => 'Extraño Ramírez',
            'email' => 'extrano@example.com',
        ]);
        $this->unrelatedPatientUser->assignRole('patient');

        // Expediente clínico
        $this->patient = Patient::create([
            'nombre' => 'Mateo',
            'primer_apellido' => 'Gómez',
            'segundo_apellido' => 'Martínez',
            'fecha_nacimiento' => '2018-05-15',
            'telefono' => '5559876543',
            'correo_electronico' => 'mateo@example.com',
        ]);

        // Vinculación familiar (Carlos es padre/tutor de Mateo)
        $this->patientUser->patients()->attach($this->patient->id, [
            'relacion' => RelationshipType::PADRE->value,
            'es_contacto_principal' => true,
        ]);

        $appointment = Appointment::create([
            'fecha_hora_inicio' => '2026-10-26 10:00:00',
            'fecha_hora_termino' => '2026-10-26 10:30:00',
            'motivo' => 'Fiebre y dolor de garganta',
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::ATENDIDA,
        ]);
        $appointment->patients()->attach($this->patient->id);

        $this->consultation = Consultation::create([
            'motivo' => 'Fiebre persistente y odinofagia',
            'exploracion_fisica' => 'Faringe hiperémica con exudado purulento',
            'diagnostico' => 'Faringoamigdalitis bacteriana',
            'tratamiento' => 'Amoxicilina con ácido clavulánico 250mg cada 8 horas por 7 días',
            'observaciones' => 'Abundantes líquidos y reposo',
            'peso' => 25.50,
            'altura' => 1.20,
            'temperatura' => 38.5,
            'presion_arterial' => '100/60',
            'frecuencia_cardiaca' => 90,
            'frecuencia_respiratoria' => 20,
            'cita_id' => $appointment->id,
            'paciente_id' => $this->patient->id,
            'medico_id' => $this->doctor->id,
        ]);
    }

    public function test_doctor_can_view_and_update_profile_and_signature(): void
    {
        Sanctum::actingAs($this->doctor);

        // 1. Ver perfil
        $showResponse = $this->getJson('/api/doctor/profile');
        $showResponse->assertOk()
            ->assertJsonPath('data.name', 'Dra. Jessie Martínez')
            ->assertJsonPath('data.cedula_profesional', '12345678')
            ->assertJsonPath('data.especialidad', 'Pediatría');

        // 2. Actualizar perfil con imagen de firma digital
        $fakeSignature = UploadedFile::fake()->image('firma.png', 300, 150);

        $updateResponse = $this->postJson('/api/doctor/profile', [
            'name' => 'Dra. Jessie Martínez Actualizada',
            'cedula_profesional' => '99988877',
            'cedula_especialidad' => '11122233',
            'especialidad' => 'Pediatría y Neonatología',
            'institucion_egreso' => 'UNAM / Hospital Infantil de México',
            'telefono_consultorio' => '555-000-1122',
            'firma_digital' => $fakeSignature,
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.name', 'Dra. Jessie Martínez Actualizada')
            ->assertJsonPath('data.cedula_profesional', '99988877')
            ->assertJsonPath('data.especialidad', 'Pediatría y Neonatología');

        $this->doctor->refresh();
        $this->assertEquals('99988877', $this->doctor->cedula_profesional);
        $this->assertEquals('Pediatría y Neonatología', $this->doctor->especialidad);
        $this->assertNotNull($this->doctor->firma_digital_path);
        Storage::disk('public')->assertExists($this->doctor->firma_digital_path);

        $activity = Activity::where('log_name', 'usuarios')
            ->where('subject_id', $this->doctor->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($activity);
        $this->assertStringContainsString('Perfil profesional médico actualizado', $activity->description);
    }

    public function test_patient_cannot_update_doctor_profile(): void
    {
        Sanctum::actingAs($this->patientUser);

        $response = $this->postJson('/api/doctor/profile', [
            'cedula_profesional' => '99988877',
        ]);

        $response->assertForbidden();
    }

    public function test_doctor_can_generate_prescription_pdf(): void
    {
        Sanctum::actingAs($this->doctor);

        $response = $this->postJson("/api/consultations/{$this->consultation->id}/prescription");

        $response->assertStatus(201)
            ->assertJsonPath('data.receta.generada', true)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'receta' => ['folio', 'generada', 'generada_el', 'url'],
                ],
            ]);

        $this->consultation->refresh();

        $this->assertNotNull($this->consultation->folio_receta);
        $this->assertMatchesRegularExpression('/^REC-\d{4}-\d{4}$/', $this->consultation->folio_receta);
        $this->assertNotNull($this->consultation->receta_pdf_path);
        $this->assertNotNull($this->consultation->receta_generada_el);

        $disk = config('filesystems.default', 'local');
        Storage::disk($disk)->assertExists($this->consultation->receta_pdf_path);

        $activity = Activity::where('log_name', 'consultas')
            ->where('subject_id', $this->consultation->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($activity);
        $this->assertStringContainsString('Receta médica en PDF generada exitosamente', $activity->description);
    }

    public function test_doctor_can_view_and_download_prescription_pdf(): void
    {
        Sanctum::actingAs($this->doctor);

        // 1. Visualización / Streaming inline (genera el PDF automáticamente si no existía)
        $streamResponse = $this->get("/api/consultations/{$this->consultation->id}/prescription");

        $streamResponse->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline;', (string) $streamResponse->headers->get('Content-Disposition'));

        $this->consultation->refresh();
        $this->assertNotNull($this->consultation->receta_pdf_path);

        // 2. Descarga directa con parámetro download
        $downloadResponse = $this->get("/api/consultations/{$this->consultation->id}/prescription?download=1");

        $downloadResponse->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment;', (string) $downloadResponse->headers->get('Content-Disposition'));
        $this->assertStringContainsString($this->consultation->folio_receta, (string) $downloadResponse->headers->get('Content-Disposition'));
    }

    public function test_linked_family_tutor_can_access_prescription_pdf(): void
    {
        Sanctum::actingAs($this->patientUser);

        $response = $this->get("/api/consultations/{$this->consultation->id}/prescription");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_unrelated_patient_cannot_access_prescription_pdf(): void
    {
        Sanctum::actingAs($this->unrelatedPatientUser);

        $response = $this->get("/api/consultations/{$this->consultation->id}/prescription");

        $response->assertForbidden();
    }
}
