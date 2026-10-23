<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\DocumentType;
use App\Enums\RelationshipType;
use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\Consultation;
use App\Models\ConsultationDocument;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ConsultationDocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $doctor;
    private User $admin;
    private User $tutor;
    private User $unrelatedPatient;
    private Patient $childPatient;
    private Consultation $consultation;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('filesystems.default', 'local'));
        Storage::fake('s3');

        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::PENDIENTE, 'nombre' => 'Pendiente']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CONFIRMADA, 'nombre' => 'Confirmada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CANCELADA, 'nombre' => 'Cancelada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::ATENDIDA, 'nombre' => 'Atendida']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::NO_ASISTIO, 'nombre' => 'No asistió']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::EN_SALA_DE_ESPERA, 'nombre' => 'En sala de espera']);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['name' => 'Admin General']);
        $this->admin->assignRole('admin');

        $this->doctor = User::factory()->create(['name' => 'Dr. Fernando Especialista']);
        $this->doctor->assignRole('doctor');

        $this->tutor = User::factory()->create(['name' => 'Carlos Papá']);
        $this->tutor->assignRole('patient');

        $this->unrelatedPatient = User::factory()->create(['name' => 'Usuario Desconocido']);
        $this->unrelatedPatient->assignRole('patient');

        $this->childPatient = Patient::create([
            'nombre' => 'Mateo',
            'primer_apellido' => 'Gómez',
            'fecha_nacimiento' => '2019-06-15',
        ]);

        // Vinculación familiar: Carlos es tutor/padre de Mateo
        $this->tutor->patients()->attach($this->childPatient->id, [
            'relacion' => RelationshipType::PADRE->value,
            'es_contacto_principal' => true,
        ]);

        $appointment = Appointment::create([
            'fecha_hora_inicio' => '2026-10-21 10:00:00',
            'fecha_hora_termino' => '2026-10-21 10:30:00',
            'motivo' => 'Revisión por traumatismo en brazo derecho',
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::ATENDIDA,
        ]);
        $appointment->patients()->attach($this->childPatient->id);

        $this->consultation = Consultation::create([
            'motivo' => 'Caída en bicicleta con dolor intenso en antebrazo',
            'diagnostico' => 'Probable fractura de cúbito y radio',
            'tratamiento' => 'Inmovilización con férula y toma urgente de rayos X',
            'cita_id' => $appointment->id,
            'paciente_id' => $this->childPatient->id,
            'medico_id' => $this->doctor->id,
        ]);
    }

    public function test_doctor_can_upload_document_to_consultation(): void
    {
        Sanctum::actingAs($this->doctor);

        $fakeFile = UploadedFile::fake()->create('rx_antebrazo.png', 1024, 'image/png');

        $response = $this->postJson("/api/consultations/{$this->consultation->id}/documents", [
            'archivo' => $fakeFile,
            'tipo_documento' => DocumentType::RADIOGRAFIA->value,
            'titulo' => 'Radiografía de Antebrazo AP y Lateral',
            'descripcion' => 'Se observa trazo de fractura no desplazada en tercio distal de cúbito.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.titulo', 'Radiografía de Antebrazo AP y Lateral')
            ->assertJsonPath('data.tipo_documento', DocumentType::RADIOGRAFIA->value)
            ->assertJsonPath('data.tipo_documento_etiqueta', 'Radiografía / Rayos X')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'consulta_id',
                    'paciente_id',
                    'titulo',
                    'tipo_documento',
                    'url_descarga',
                    'peso_legible',
                    'creado_el',
                ],
            ]);

        $this->assertDatabaseHas('consulta_archivos', [
            'consulta_id' => $this->consultation->id,
            'paciente_id' => $this->childPatient->id,
            'tipo_documento' => DocumentType::RADIOGRAFIA->value,
            'titulo' => 'Radiografía de Antebrazo AP y Lateral',
            'nombre_archivo_original' => 'rx_antebrazo.png',
        ]);

        $doc = ConsultationDocument::first();
        $disk = config('filesystems.default', 'local');
        Storage::disk($disk)->assertExists($doc->ruta_archivo);

        $activity = Activity::where('log_name', 'consultas')
            ->where('subject_id', $doc->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($activity);
        $this->assertStringContainsString('Documento clínico anexo subido exitosamente', $activity->description);
    }

    public function test_doctor_and_tutor_can_list_consultation_documents(): void
    {
        $fakeFile = UploadedFile::fake()->create('biometria.pdf', 500, 'application/pdf');

        $doc = ConsultationDocument::create([
            'consulta_id' => $this->consultation->id,
            'paciente_id' => $this->childPatient->id,
            'medico_id' => $this->doctor->id,
            'tipo_documento' => DocumentType::LABORATORIO->value,
            'titulo' => 'Biometría Hemática Completa',
            'nombre_archivo_original' => 'biometria.pdf',
            'ruta_archivo' => 'consultas/' . $this->consultation->id . '/estudios/bio.pdf',
            'disk' => (string) config('filesystems.default', 'local'),
            'mime_type' => 'application/pdf',
            'peso_bytes' => 500000,
        ]);

        // 1. Doctor lista documentos
        Sanctum::actingAs($this->doctor);
        $responseDoctor = $this->getJson("/api/consultations/{$this->consultation->id}/documents");
        $responseDoctor->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Biometría Hemática Completa');

        // 2. Tutor familiar vinculado lista documentos de su hijo
        Sanctum::actingAs($this->tutor);
        $responseTutor = $this->getJson("/api/consultations/{$this->consultation->id}/documents");
        $responseTutor->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_unrelated_patient_cannot_access_consultation_documents(): void
    {
        Sanctum::actingAs($this->unrelatedPatient);

        $response = $this->getJson("/api/consultations/{$this->consultation->id}/documents");

        $response->assertForbidden();
    }

    public function test_doctor_can_soft_delete_document_and_file_is_preserved(): void
    {
        $disk = (string) config('filesystems.default', 'local');
        $storedPath = 'consultas/' . $this->consultation->id . '/estudios/rx_test.png';
        Storage::disk($disk)->put($storedPath, 'contenido binario simulado');

        $document = ConsultationDocument::create([
            'consulta_id' => $this->consultation->id,
            'paciente_id' => $this->childPatient->id,
            'medico_id' => $this->doctor->id,
            'tipo_documento' => DocumentType::RADIOGRAFIA->value,
            'titulo' => 'Radiografía previa',
            'nombre_archivo_original' => 'rx_test.png',
            'ruta_archivo' => $storedPath,
            'disk' => $disk,
            'mime_type' => 'image/png',
            'peso_bytes' => 1234,
        ]);

        Sanctum::actingAs($this->doctor);

        // 1. Ejecutar DELETE
        $response = $this->deleteJson("/api/consultations/{$this->consultation->id}/documents/{$document->id}");
        $response->assertOk()
            ->assertJsonPath('message', 'Documento clínico eliminado lógicamente exitosamente (soft-delete).');

        // 2. Verificar que el registro está marcado con deleted_at (Soft Delete)
        $this->assertNull(ConsultationDocument::find($document->id));
        $this->assertNotNull(ConsultationDocument::withTrashed()->find($document->id));
        $this->assertTrue(ConsultationDocument::withTrashed()->find($document->id)->trashed());

        // 3. El archivo físico sigue existiendo en el disco para trazabilidad médico-legal
        Storage::disk($disk)->assertExists($storedPath);

        // 4. Auditoría en activitylog
        $activity = Activity::where('log_name', 'consultas')
            ->where('subject_id', $document->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($activity);
        $this->assertStringContainsString('eliminado lógicamente (soft-delete)', $activity->description);
    }

    public function test_admin_can_restore_soft_deleted_document(): void
    {
        $document = ConsultationDocument::create([
            'consulta_id' => $this->consultation->id,
            'paciente_id' => $this->childPatient->id,
            'medico_id' => $this->doctor->id,
            'tipo_documento' => DocumentType::LABORATORIO->value,
            'titulo' => 'Estudio a restaurar',
            'nombre_archivo_original' => 'test.pdf',
            'ruta_archivo' => 'consultas/1/estudios/test.pdf',
            'disk' => (string) config('filesystems.default', 'local'),
            'mime_type' => 'application/pdf',
            'peso_bytes' => 1000,
        ]);

        $document->delete();
        $this->assertTrue($document->trashed());

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/consultations/{$this->consultation->id}/documents/{$document->id}/restore");

        $response->assertOk()
            ->assertJsonPath('data.titulo', 'Estudio a restaurar');

        $this->assertFalse($document->fresh()->trashed());
    }

    public function test_can_filter_patient_global_documents_by_type(): void
    {
        $disk = (string) config('filesystems.default', 'local');

        ConsultationDocument::create([
            'consulta_id' => $this->consultation->id,
            'paciente_id' => $this->childPatient->id,
            'medico_id' => $this->doctor->id,
            'tipo_documento' => DocumentType::RADIOGRAFIA->value,
            'titulo' => 'Rx Antebrazo',
            'nombre_archivo_original' => 'rx.jpg',
            'ruta_archivo' => 'consultas/1/rx.jpg',
            'disk' => $disk,
            'mime_type' => 'image/jpeg',
            'peso_bytes' => 2000,
        ]);

        ConsultationDocument::create([
            'consulta_id' => $this->consultation->id,
            'paciente_id' => $this->childPatient->id,
            'medico_id' => $this->doctor->id,
            'tipo_documento' => DocumentType::LABORATORIO->value,
            'titulo' => 'Química Sanguínea',
            'nombre_archivo_original' => 'quimica.pdf',
            'ruta_archivo' => 'consultas/1/quimica.pdf',
            'disk' => $disk,
            'mime_type' => 'application/pdf',
            'peso_bytes' => 1500,
        ]);

        Sanctum::actingAs($this->tutor);

        // 1. Filtrar solo radiografías
        $responseRx = $this->getJson("/api/patients/{$this->childPatient->id}/documents?tipo_documento=" . DocumentType::RADIOGRAFIA->value);
        $responseRx->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tipo_documento', DocumentType::RADIOGRAFIA->value);

        // 2. Sin filtro: retorna ambos documentos
        $responseAll = $this->getJson("/api/patients/{$this->childPatient->id}/documents");
        $responseAll->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
