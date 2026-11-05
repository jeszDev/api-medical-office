<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DoctorProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_login_returns_doctor_profile_when_user_has_doctor_role(): void
    {
        $user = User::factory()->create([
            'email' => 'doctor@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole('doctor');

        $doctor = Doctor::factory()->create([
            'user_id' => $user->id,
            'cedula_profesional' => '99887766',
            'universidad' => 'UNAM',
            'telefono_consultorio' => '5551234567',
        ]);

        $specialty = Specialty::create(['nombre' => 'Pediatría']);
        $doctor->specialties()->attach($specialty->id, [
            'cedula_especialidad' => '11223344',
            'es_principal' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'doctor@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.email', 'doctor@example.com')
            ->assertJsonPath('user.doctor.cedula_profesional', '99887766')
            ->assertJsonPath('user.doctor.universidad', 'UNAM')
            ->assertJsonPath('user.doctor.especialidades.0.nombre', 'Pediatría')
            ->assertJsonPath('user.doctor.especialidades.0.cedula_especialidad', '11223344')
            ->assertJsonPath('user.doctor.especialidades.0.es_principal', true);
    }

    public function test_login_does_not_return_doctor_field_when_user_is_not_doctor(): void
    {
        $user = User::factory()->create([
            'email' => 'patient@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole('patient');

        $response = $this->postJson('/api/auth/login', [
            'email' => 'patient@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonMissingPath('user.doctor');
    }

    public function test_doctor_can_view_profile_endpoint(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $doctor = Doctor::factory()->create([
            'user_id' => $user->id,
            'cedula_profesional' => '55443322',
        ]);

        $specialty = Specialty::create(['nombre' => 'Cardiología']);
        $doctor->specialties()->attach($specialty->id, [
            'cedula_especialidad' => '77889900',
            'es_principal' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/doctor/profile');

        $response->assertStatus(200)
            ->assertJsonPath('doctor.id', $doctor->id)
            ->assertJsonPath('doctor.cedula_profesional', '55443322')
            ->assertJsonPath('doctor.especialidades.0.nombre', 'Cardiología');
    }

    public function test_non_doctor_cannot_access_doctor_profile_endpoint(): void
    {
        $user = User::factory()->create();
        $user->assignRole('patient');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/doctor/profile');

        $response->assertStatus(403);
    }

    public function test_doctor_can_update_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $doctor = Doctor::factory()->create([
            'user_id' => $user->id,
            'cedula_profesional' => '12345678',
            'telefono_consultorio' => '5550001111',
            'direccion_consultorio' => 'Calle Antigua 123',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/doctor/profile', [
            'telefono_consultorio' => '5559998888',
            'direccion_consultorio' => 'Nueva Avenida Médica 456, Consultorio 202',
            'universidad' => 'Universidad de Guadalajara',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Perfil médico actualizado correctamente')
            ->assertJsonPath('doctor.telefono_consultorio', '5559998888')
            ->assertJsonPath('doctor.direccion_consultorio', 'Nueva Avenida Médica 456, Consultorio 202')
            ->assertJsonPath('doctor.universidad', 'Universidad de Guadalajara');

        $this->assertDatabaseHas('medicos', [
            'id' => $doctor->id,
            'telefono_consultorio' => '5559998888',
            'direccion_consultorio' => 'Nueva Avenida Médica 456, Consultorio 202',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'medicos',
            'description' => 'Perfil médico actualizado',
        ]);
    }

    public function test_doctor_update_validation_fails_on_duplicate_cedula(): void
    {
        Doctor::factory()->create(['cedula_profesional' => 'DUPLICATE123']);

        $user = User::factory()->create();
        $user->assignRole('doctor');
        Doctor::factory()->create([
            'user_id' => $user->id,
            'cedula_profesional' => 'ORIGINAL456',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/doctor/profile', [
            'cedula_profesional' => 'DUPLICATE123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cedula_profesional']);
    }
}

