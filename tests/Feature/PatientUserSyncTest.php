<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RelationshipType;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PatientUserSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $receptionist;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->receptionist = User::factory()->create();
        $this->receptionist->assignRole('receptionist');
    }

    public function test_receptionist_updates_titular_patient_replicates_to_user(): void
    {
        Sanctum::actingAs($this->receptionist);

        $user = User::factory()->create([
            'nombre' => 'Carlos',
            'primer_apellido' => 'Gómez',
            'segundo_apellido' => 'Hernández',
            'curp' => 'GOHC820512HDFR0001',
            'email' => 'carlos.original@gmail.com',
        ]);
        $user->assignRole('patient');

        $patient = Patient::factory()->create([
            'nombre' => 'Carlos',
            'primer_apellido' => 'Gómez',
            'segundo_apellido' => 'Hernández',
            'curp' => 'GOHC820512HDFR0001',
            'correo_electronico' => 'carlos.original@gmail.com',
        ]);

        $user->patients()->attach($patient->id, [
            'relacion' => RelationshipType::TITULAR->value,
            'es_contacto_principal' => true,
        ]);

        // La recepcionista actualiza los datos del paciente titular
        $response = $this->putJson("/api/patients/{$patient->id}", [
            'nombre' => 'Carlos Alberto',
            'primer_apellido' => 'Gómez',
            'segundo_apellido' => 'Hernández',
            'curp' => 'GOHC820512HDFR0099',
            'correo_electronico' => 'carlos.nuevo@gmail.com',
        ]);

        $response->assertStatus(200);

        // 1. Verificación en pacientes
        $this->assertDatabaseHas('pacientes', [
            'id' => $patient->id,
            'nombre' => 'Carlos Alberto',
            'curp' => 'GOHC820512HDFR0099',
            'correo_electronico' => 'carlos.nuevo@gmail.com',
        ]);

        // 2. Verificación de replicación en users
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nombre' => 'Carlos Alberto',
            'curp' => 'GOHC820512HDFR0099',
            'email' => 'carlos.nuevo@gmail.com',
        ]);
    }

    public function test_receptionist_updates_dependent_patient_does_not_alter_tutor_user(): void
    {
        Sanctum::actingAs($this->receptionist);

        // Padre (Tutor)
        $tutor = User::factory()->create([
            'nombre' => 'Carlos',
            'primer_apellido' => 'Gómez',
            'curp' => 'GOHC820512HDFR0001',
            'email' => 'carlos.padre@gmail.com',
        ]);
        $tutor->assignRole('patient');

        // Hijo (Dependiente)
        $childPatient = Patient::factory()->create([
            'nombre' => 'Mateo',
            'primer_apellido' => 'Gómez',
            'curp' => 'GOMM170318HDFR0002',
            'correo_electronico' => null,
        ]);

        $tutor->patients()->attach($childPatient->id, [
            'relacion' => RelationshipType::HIJO->value,
            'es_contacto_principal' => false,
        ]);

        // La recepcionista actualiza el nombre y CURP del hijo
        $response = $this->putJson("/api/patients/{$childPatient->id}", [
            'nombre' => 'Mateo Daniel',
            'primer_apellido' => 'Gómez',
            'curp' => 'GOMM170318HDFR0088',
        ]);

        $response->assertStatus(200);

        // 1. El paciente dependiente se actualizó
        $this->assertDatabaseHas('pacientes', [
            'id' => $childPatient->id,
            'nombre' => 'Mateo Daniel',
            'curp' => 'GOMM170318HDFR0088',
        ]);

        // 2. El usuario tutor NO sufrió ninguna modificación
        $this->assertDatabaseHas('users', [
            'id' => $tutor->id,
            'nombre' => 'Carlos',
            'curp' => 'GOHC820512HDFR0001',
            'email' => 'carlos.padre@gmail.com',
        ]);
    }

    public function test_receptionist_updates_titular_patient_with_colliding_email_does_not_crash(): void
    {
        Sanctum::actingAs($this->receptionist);

        // Usuario existente con el correo colisionante
        User::factory()->create(['email' => 'ocupado@gmail.com']);

        $user = User::factory()->create([
            'nombre' => 'Roberto',
            'email' => 'roberto.libre@gmail.com',
        ]);
        $user->assignRole('patient');

        $patient = Patient::factory()->create([
            'nombre' => 'Roberto',
            'correo_electronico' => 'roberto.libre@gmail.com',
        ]);

        $user->patients()->attach($patient->id, [
            'relacion' => RelationshipType::TITULAR->value,
            'es_contacto_principal' => true,
        ]);

        // Recepcionista intenta poner un correo ya en uso por otro usuario
        $response = $this->putJson("/api/patients/{$patient->id}", [
            'nombre' => 'Roberto Editado',
            'primer_apellido' => $patient->primer_apellido,
            'correo_electronico' => 'ocupado@gmail.com',
        ]);

        $response->assertStatus(200);

        // El paciente toma el correo de contacto
        $this->assertDatabaseHas('pacientes', [
            'id' => $patient->id,
            'nombre' => 'Roberto Editado',
            'correo_electronico' => 'ocupado@gmail.com',
        ]);

        // El usuario se actualiza en su nombre pero CONSERVA su email sin provocar crash de clave duplicada
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nombre' => 'Roberto Editado',
            'email' => 'roberto.libre@gmail.com',
        ]);
    }

    public function test_user_updates_own_profile_replicates_to_titular_patient(): void
    {
        $user = User::factory()->create([
            'nombre' => 'Ana',
            'primer_apellido' => 'Martínez',
            'segundo_apellido' => 'Sánchez',
            'curp' => 'MARA860614MDFR0005',
            'email' => 'ana.original@gmail.com',
        ]);
        $user->assignRole('patient');

        $patient = Patient::factory()->create([
            'nombre' => 'Ana',
            'primer_apellido' => 'Martínez',
            'segundo_apellido' => 'Sánchez',
            'curp' => 'MARA860614MDFR0005',
            'correo_electronico' => 'ana.original@gmail.com',
        ]);

        $user->patients()->attach($patient->id, [
            'relacion' => RelationshipType::TITULAR->value,
            'es_contacto_principal' => true,
        ]);

        Sanctum::actingAs($user);

        // El usuario actualiza su perfil en la plataforma
        $response = $this->putJson('/api/user', [
            'nombre' => 'Ana Patricia',
            'primer_apellido' => 'Martínez',
            'segundo_apellido' => 'Sánchez',
            'curp' => 'MARA860614MDFR0077',
            'email' => 'ana.nuevo@gmail.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.nombre', 'Ana Patricia')
            ->assertJsonPath('user.email', 'ana.nuevo@gmail.com')
            ->assertJsonPath('user.patient.nombre', 'Ana Patricia')
            ->assertJsonPath('user.patient.curp', 'MARA860614MDFR0077')
            ->assertJsonPath('user.patient.correo_electronico', 'ana.nuevo@gmail.com');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nombre' => 'Ana Patricia',
            'curp' => 'MARA860614MDFR0077',
            'email' => 'ana.nuevo@gmail.com',
        ]);

        $this->assertDatabaseHas('pacientes', [
            'id' => $patient->id,
            'nombre' => 'Ana Patricia',
            'curp' => 'MARA860614MDFR0077',
            'correo_electronico' => 'ana.nuevo@gmail.com',
        ]);
    }

    public function test_user_without_patient_record_updates_own_profile_successfully(): void
    {
        $independentUser = User::factory()->create([
            'nombre' => 'Recepcionista',
            'primer_apellido' => 'Pérez',
            'email' => 'recepcionista@gmail.com',
        ]);

        Sanctum::actingAs($independentUser);

        $response = $this->putJson('/api/user', [
            'nombre' => 'Recepcionista Actualizada',
            'primer_apellido' => 'Pérez',
            'email' => 'recepcionista.nueva@gmail.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.nombre', 'Recepcionista Actualizada')
            ->assertJsonPath('user.email', 'recepcionista.nueva@gmail.com');

        $this->assertDatabaseHas('users', [
            'id' => $independentUser->id,
            'nombre' => 'Recepcionista Actualizada',
            'email' => 'recepcionista.nueva@gmail.com',
        ]);
    }
}

