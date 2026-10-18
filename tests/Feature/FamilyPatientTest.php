<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RelationshipType;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class FamilyPatientTest extends TestCase
{
    use RefreshDatabase;

    private User $fatherUser;
    private User $motherUser;
    private User $unrelatedUser;
    private User $receptionist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->fatherUser = User::factory()->create(['nombre' => 'Carlos', 'primer_apellido' => 'Padre', 'email' => 'padre@example.com']);
        $this->fatherUser->assignRole('patient');

        $this->motherUser = User::factory()->create(['nombre' => 'Ana', 'primer_apellido' => 'Madre', 'email' => 'madre@example.com']);
        $this->motherUser->assignRole('patient');

        $this->unrelatedUser = User::factory()->create(['nombre' => 'Juan', 'primer_apellido' => 'Desconocido', 'email' => 'juan@example.com']);
        $this->unrelatedUser->assignRole('patient');

        $this->receptionist = User::factory()->create(['nombre' => 'Recepcionista', 'primer_apellido' => 'Maria']);
        $this->receptionist->assignRole('receptionist');
    }

    public function test_user_can_view_only_their_linked_family_members(): void
    {
        $patientFather = Patient::create([
            'nombre' => 'Carlos',
            'primer_apellido' => 'Padre',
        ]);
        $this->fatherUser->patients()->attach($patientFather->id, [
            'relacion' => RelationshipType::TITULAR->value,
            'es_contacto_principal' => true,
        ]);

        $childPatient = Patient::create([
            'nombre' => 'Mateo',
            'primer_apellido' => 'Padre',
        ]);
        $this->fatherUser->patients()->attach($childPatient->id, [
            'relacion' => RelationshipType::HIJO->value,
            'es_contacto_principal' => false,
        ]);

        $unrelatedPatient = Patient::create([
            'nombre' => 'Extraño',
            'primer_apellido' => 'López',
        ]);
        $this->unrelatedUser->patients()->attach($unrelatedPatient->id, [
            'relacion' => RelationshipType::TITULAR->value,
            'es_contacto_principal' => true,
        ]);

        Sanctum::actingAs($this->fatherUser);

        $response = $this->getJson('/api/my-patients');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.nombre', 'Mateo')
            ->assertJsonPath('data.0.vinculo_familiar.relacion', RelationshipType::HIJO->value)
            ->assertJsonPath('data.0.vinculo_familiar.relacion_texto', 'Hijo/a')
            ->assertJsonPath('data.1.nombre', 'Carlos')
            ->assertJsonPath('data.1.vinculo_familiar.relacion', RelationshipType::TITULAR->value);
    }

    public function test_user_can_register_dependent_and_it_links_automatically(): void
    {
        Sanctum::actingAs($this->fatherUser);

        $payload = [
            'nombre' => 'Sofía',
            'primer_apellido' => 'Padre',
            'segundo_apellido' => 'Madre',
            'fecha_nacimiento' => '2020-03-15',
            'curp' => 'PADS200315MDFR0001',
            'relacion' => 'hijo',
            'es_contacto_principal' => true,
        ];

        $response = $this->postJson('/api/my-patients', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.nombre', 'Sofía')
            ->assertJsonPath('data.primer_apellido', 'Padre')
            ->assertJsonPath('data.vinculo_familiar.relacion', 'hijo')
            ->assertJsonPath('data.vinculo_familiar.relacion_texto', 'Hijo/a')
            ->assertJsonPath('data.vinculo_familiar.es_contacto_principal', true);

        $this->assertDatabaseHas('pacientes', [
            'nombre' => 'Sofía',
            'curp' => 'PADS200315MDFR0001',
        ]);

        $this->assertDatabaseHas('paciente_usuario', [
            'user_id' => $this->fatherUser->id,
            'relacion' => 'hijo',
            'es_contacto_principal' => 1,
        ]);

        // Verificamos registro de auditoría clínica
        $activity = Activity::where('log_name', 'pacientes')
            ->where('description', 'Tutor o familiar vinculado a paciente')
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($this->fatherUser->id, $activity->causer_id);
    }

    public function test_receptionist_can_link_second_tutor_to_same_patient(): void
    {
        $child = Patient::create([
            'nombre' => 'Mateo',
            'primer_apellido' => 'Padre',
        ]);

        // El padre ya lo tiene vinculado
        $this->fatherUser->patients()->attach($child->id, [
            'relacion' => RelationshipType::HIJO->value,
            'es_contacto_principal' => true,
        ]);

        // Recepcionista vincula a la madre también
        Sanctum::actingAs($this->receptionist);

        $response = $this->postJson("/api/patients/{$child->id}/guardians", [
            'user_id' => $this->motherUser->id,
            'relacion' => 'madre',
            'es_contacto_principal' => false,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.user_id', $this->motherUser->id)
            ->assertJsonPath('data.relacion', 'madre')
            ->assertJsonPath('data.relacion_texto', 'Madre');

        // Ambos padres tienen acceso a ver al hijo
        Sanctum::actingAs($this->motherUser);
        $motherList = $this->getJson('/api/my-patients');
        $motherList->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $child->id);

        Sanctum::actingAs($this->fatherUser);
        $fatherList = $this->getJson('/api/my-patients');
        $fatherList->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $child->id);
    }

    public function test_patient_user_cannot_access_unrelated_patient_via_policy(): void
    {
        $child = Patient::create([
            'nombre' => 'Mateo',
            'primer_apellido' => 'Padre',
        ]);
        $this->fatherUser->patients()->attach($child->id, [
            'relacion' => RelationshipType::HIJO->value,
            'es_contacto_principal' => true,
        ]);

        $strangerPatient = Patient::create([
            'nombre' => 'Desconocido',
            'primer_apellido' => 'Gómez',
        ]);

        Sanctum::actingAs($this->fatherUser);

        // Puede ver a su hijo
        $canSeeChild = $this->getJson("/api/patients/{$child->id}");
        $canSeeChild->assertStatus(200);

        // NO puede ver a un paciente no vinculado
        $cannotSeeStranger = $this->getJson("/api/patients/{$strangerPatient->id}");
        $cannotSeeStranger->assertStatus(403);
    }

    public function test_receptionist_can_view_guardians_of_a_patient(): void
    {
        $child = Patient::create([
            'nombre' => 'Mateo',
            'primer_apellido' => 'Padre',
        ]);

        $this->fatherUser->patients()->attach($child->id, [
            'relacion' => RelationshipType::PADRE->value,
            'es_contacto_principal' => true,
        ]);

        Sanctum::actingAs($this->receptionist);

        $response = $this->getJson("/api/patients/{$child->id}/guardians");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->fatherUser->id)
            ->assertJsonPath('data.0.relacion', 'padre')
            ->assertJsonPath('data.0.relacion_texto', 'Padre')
            ->assertJsonPath('data.0.es_contacto_principal', true);
    }
}
