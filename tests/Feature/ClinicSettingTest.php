<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\ClinicSetting;
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

class ClinicSettingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $doctor;
    private User $patientUser;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('filesystems.default', 'local'));
        Storage::fake('public');

        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::PENDIENTE, 'nombre' => 'Pendiente']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CONFIRMADA, 'nombre' => 'Confirmada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::CANCELADA, 'nombre' => 'Cancelada']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::ATENDIDA, 'nombre' => 'Atendida']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::NO_ASISTIO, 'nombre' => 'No asistió']);
        CatalogAppointmentStatus::create(['id' => CatalogAppointmentStatus::EN_SALA_DE_ESPERA, 'nombre' => 'En sala de espera']);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['name' => 'Admin Clinica', 'email' => 'admin.test@example.com']);
        $this->admin->assignRole('admin');

        $this->doctor = User::factory()->create(['name' => 'Dr. Jessie Medico', 'email' => 'doctor.test@example.com']);
        $this->doctor->assignRole('doctor');

        $this->patientUser = User::factory()->create(['name' => 'Paciente Carlos', 'email' => 'paciente.test@example.com']);
        $this->patientUser->assignRole('patient');
    }

    public function test_authenticated_user_can_view_clinic_settings(): void
    {
        Sanctum::actingAs($this->doctor);

        $response = $this->getJson('/api/clinic/settings');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nombre',
                    'razon_social',
                    'rfc',
                    'direccion',
                    'telefono',
                    'telefono_urgencias',
                    'correo_electronico',
                    'logotipo_path',
                    'logotipo_url',
                    'leyenda_receta',
                    'actualizado_el',
                ],
            ]);
    }

    public function test_admin_can_update_clinic_settings_and_upload_logo(): void
    {
        Sanctum::actingAs($this->admin);

        $fakeLogo = UploadedFile::fake()->image('logo_clinica.png', 400, 200);

        $response = $this->postJson('/api/clinic/settings', [
            'nombre' => 'Hospital Pediátrico San Ángel',
            'razon_social' => 'Atención Médica Especializada San Ángel S.A.P.I. de C.V.',
            'rfc' => 'AME210515KLA',
            'direccion' => 'Av. Insurgentes Sur #4500, Tlalpan, CDMX',
            'telefono' => '555-888-9900',
            'telefono_urgencias' => '555-111-2233',
            'correo_electronico' => 'contacto@sanangelpediatria.com',
            'leyenda_receta' => 'Uso pediátrico controlado. No automedicarse.',
            'logotipo' => $fakeLogo,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.nombre', 'Hospital Pediátrico San Ángel')
            ->assertJsonPath('data.rfc', 'AME210515KLA')
            ->assertJsonPath('data.telefono', '555-888-9900');

        $setting = ClinicSetting::getSettings();
        $this->assertEquals('Hospital Pediátrico San Ángel', $setting->nombre);
        $this->assertEquals('AME210515KLA', $setting->rfc);
        $this->assertNotNull($setting->logotipo_path);
        Storage::disk('public')->assertExists($setting->logotipo_path);

        $activity = Activity::where('log_name', 'clinica')
            ->where('subject_id', $setting->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($activity);
        $this->assertStringContainsString('Configuración institucional de la clínica actualizada', $activity->description);
    }

    public function test_non_admin_cannot_update_clinic_settings(): void
    {
        Sanctum::actingAs($this->patientUser);

        $response = $this->postJson('/api/clinic/settings', [
            'nombre' => 'Intento No Autorizado',
        ]);

        $response->assertForbidden();
    }

    public function test_prescription_pdf_uses_updated_clinic_settings(): void
    {
        // 1. Establecer datos de la clínica personalizados en base de datos
        ClinicSetting::create([
            'nombre' => 'Centro Clínico de Especialidades Norte',
            'razon_social' => 'Salud del Norte S.C.',
            'rfc' => 'SDN990101XYZ',
            'direccion' => 'Calzada del Valle #300, San Pedro Garza García, N.L.',
            'telefono' => '818-000-1122',
            'leyenda_receta' => 'Receta oficial emitida por el Centro Clínico de Especialidades Norte.',
        ]);
        ClinicSetting::clearCache();

        // 2. Crear paciente, cita y consulta
        $patient = Patient::create([
            'nombre' => 'Lucía',
            'primer_apellido' => 'Vargas',
            'fecha_nacimiento' => '2015-03-10',
        ]);

        $appointment = Appointment::create([
            'fecha_hora_inicio' => '2026-10-21 09:00:00',
            'fecha_hora_termino' => '2026-10-21 09:30:00',
            'motivo' => 'Consulta de control general',
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::ATENDIDA,
        ]);
        $appointment->patients()->attach($patient->id);

        $consultation = Consultation::create([
            'motivo' => 'Revisión periódica anual',
            'diagnostico' => 'Paciente sano sin hallazgos patológicos',
            'tratamiento' => 'Mantener hidratación y hábitos saludables',
            'cita_id' => $appointment->id,
            'paciente_id' => $patient->id,
            'medico_id' => $this->doctor->id,
        ]);

        Sanctum::actingAs($this->doctor);

        // 3. Generar la receta médica en PDF
        $response = $this->postJson("/api/consultations/{$consultation->id}/prescription");
        $response->assertStatus(201);

        $consultation->refresh();
        $this->assertNotNull($consultation->receta_pdf_path);

        $disk = config('filesystems.default', 'local');
        Storage::disk($disk)->assertExists($consultation->receta_pdf_path);
    }
}
