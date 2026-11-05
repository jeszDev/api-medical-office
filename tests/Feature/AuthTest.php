<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RelationshipType;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create([
            'email' => 'doctor@example.com',
            'password' => Hash::make('password123'),
        ]);
        $this->user->assignRole('doctor');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'doctor@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'email', 'roles', 'permissions'],
                'token',
            ])
            ->assertJsonPath('user.email', 'doctor@example.com')
            ->assertJsonPath('user.roles.0', 'doctor');

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'Inicio de sesión exitoso',
            'causer_id' => $this->user->id,
        ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'doctor@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_register_successfully(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nombre' => 'Claudia',
            'primer_apellido' => 'Ramos',
            'email' => 'claudia@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'nombre', 'primer_apellido', 'nombre_completo', 'email'],
                'token',
            ])
            ->assertJsonPath('user.email', 'claudia@example.com')
            ->assertJsonPath('user.nombre_completo', 'Claudia Ramos');

        $this->assertDatabaseHas('users', [
            'email' => 'claudia@example.com',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'Usuario registrado',
        ]);
    }

    public function test_user_can_register_with_curp_and_full_name_fields(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'curp' => 'RAMC900101MDFR0009',
            'nombre' => 'Claudia',
            'primer_apellido' => 'Ramos',
            'segundo_apellido' => 'López',
            'email' => 'claudia.ramos@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('user.curp', 'RAMC900101MDFR0009')
            ->assertJsonPath('user.nombre', 'Claudia')
            ->assertJsonPath('user.primer_apellido', 'Ramos')
            ->assertJsonPath('user.segundo_apellido', 'López')
            ->assertJsonPath('user.nombre_completo', 'Claudia Ramos López');

        $this->assertDatabaseHas('users', [
            'curp' => 'RAMC900101MDFR0009',
            'nombre' => 'Claudia',
            'primer_apellido' => 'Ramos',
            'email' => 'claudia.ramos@example.com',
        ]);
    }

    public function test_register_validation_fails_on_duplicate_email(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nombre' => 'Otro',
            'primer_apellido' => 'Doctor',
            'email' => 'doctor@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_check_status_and_renew_token(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/auth/check-status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'email'],
                'token',
            ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sesión cerrada correctamente',
            ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'Cierre de sesión',
            'causer_id' => $this->user->id,
        ]);
    }

    public function test_login_returns_patient_data_when_user_is_patient(): void
    {
        $patientUser = User::factory()->create([
            'email' => 'paciente@example.com',
            'password' => Hash::make('password123'),
        ]);
        $patientUser->assignRole('patient');

        $patient = Patient::factory()->create([
            'nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'telefono' => '5551234567',
        ]);

        $patientUser->patients()->attach($patient->id, [
            'relacion' => RelationshipType::TITULAR->value,
            'es_contacto_principal' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'paciente@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.email', 'paciente@example.com')
            ->assertJsonPath('user.roles.0', 'patient')
            ->assertJsonPath('user.patient.id', $patient->id)
            ->assertJsonPath('user.patient.nombre', 'Juan')
            ->assertJsonPath('user.patient.primer_apellido', 'Pérez')
            ->assertJsonPath('user.patient.vinculo_familiar.relacion', RelationshipType::TITULAR->value)
            ->assertJsonMissingPath('user.doctor');
    }

    public function test_login_returns_both_doctor_and_patient_data_when_user_has_dual_roles(): void
    {
        $dualUser = User::factory()->create([
            'email' => 'dual@example.com',
            'password' => Hash::make('password123'),
        ]);
        $dualUser->assignRole(['doctor', 'patient']);

        // Perfil médico
        $doctor = Doctor::factory()->create([
            'user_id' => $dualUser->id,
            'cedula_profesional' => 'CEDULA888',
        ]);
        $specialty = Specialty::create(['nombre' => 'Oftalmología']);
        $doctor->specialties()->attach($specialty->id, [
            'cedula_especialidad' => 'ESP999',
            'es_principal' => true,
        ]);

        // Expediente de paciente
        $patient = Patient::factory()->create([
            'nombre' => 'Doctora',
            'primer_apellido' => 'Paciente',
        ]);
        $dualUser->patients()->attach($patient->id, [
            'relacion' => RelationshipType::TITULAR->value,
            'es_contacto_principal' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'dual@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.email', 'dual@example.com')
            ->assertJsonPath('user.doctor.cedula_profesional', 'CEDULA888')
            ->assertJsonPath('user.doctor.especialidades.0.nombre', 'Oftalmología')
            ->assertJsonPath('user.patient.id', $patient->id)
            ->assertJsonPath('user.patient.nombre', 'Doctora')
            ->assertJsonPath('user.patient.vinculo_familiar.relacion', RelationshipType::TITULAR->value);
    }
}
