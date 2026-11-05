<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RelationshipType;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_a_doctor_profile(): void
    {
        $user = User::factory()->create();
        $doctor = Doctor::factory()->create([
            'user_id' => $user->id,
            'cedula_profesional' => '98765432',
            'universidad' => 'UNAM Facultad de Medicina',
            'telefono_consultorio' => '5559876543',
            'direccion_consultorio' => 'Calle Médica 100, Consultorio 5',
        ]);

        $this->assertDatabaseHas('medicos', [
            'id' => $doctor->id,
            'user_id' => $user->id,
            'cedula_profesional' => '98765432',
        ]);

        $this->assertTrue($user->doctor->is($doctor));
        $this->assertTrue($user->doctorProfile->is($doctor));
        $this->assertTrue($doctor->user->is($user));
    }

    public function test_doctor_can_have_multiple_specialties_with_pivot_data(): void
    {
        $doctor = Doctor::factory()->create();

        $pediatria = Specialty::create([
            'nombre' => 'Pediatría',
            'descripcion' => 'Atención de niños',
        ]);

        $cardiologia = Specialty::create([
            'nombre' => 'Cardiología Pediátrica',
            'descripcion' => 'Subespecialidad en corazón infantil',
        ]);

        $doctor->specialties()->attach($pediatria->id, [
            'cedula_especialidad' => '11223344',
            'universidad_especialidad' => 'Hospital Infantil',
            'es_principal' => true,
        ]);

        $doctor->specialties()->attach($cardiologia->id, [
            'cedula_especialidad' => '55667788',
            'universidad_especialidad' => 'Instituto Nacional de Cardiología',
            'es_principal' => false,
        ]);

        $this->assertCount(2, $doctor->specialties);
        $this->assertEquals('Pediatría', $doctor->primarySpecialty()->nombre);
        $this->assertEquals('11223344', $doctor->specialties->first()->pivot->cedula_especialidad);

        $this->assertTrue($pediatria->doctors->contains($doctor));
        $this->assertTrue($cardiologia->doctors->contains($doctor));
    }

    public function test_deleting_user_cascades_and_deletes_doctor_profile(): void
    {
        $user = User::factory()->create();
        $doctor = Doctor::factory()->create(['user_id' => $user->id]);
        $specialty = Specialty::create(['nombre' => 'Dermatología']);

        $doctor->specialties()->attach($specialty->id, [
            'cedula_especialidad' => '99887766',
        ]);

        $this->assertDatabaseHas('medicos', ['id' => $doctor->id]);
        $this->assertDatabaseHas('especialidad_medico', ['medico_id' => $doctor->id]);

        $user->delete();

        $this->assertDatabaseMissing('medicos', ['id' => $doctor->id]);
        $this->assertDatabaseMissing('especialidad_medico', ['medico_id' => $doctor->id]);
        $this->assertDatabaseHas('especialidades', ['id' => $specialty->id]);
    }

    public function test_user_can_simultaneously_have_doctor_profile_and_patient_record(): void
    {
        $user = User::factory()->create();

        // 1. Asignar perfil médico
        $doctor = Doctor::factory()->create([
            'user_id' => $user->id,
            'cedula_profesional' => '12345678',
        ]);

        // 2. Asignar expediente clínico como paciente
        $patient = Patient::factory()->create([
            'curp' => $user->curp,
            'nombre' => $user->nombre,
            'primer_apellido' => $user->primer_apellido,
        ]);

        $user->patients()->attach($patient->id, [
            'relacion' => RelationshipType::TITULAR->value,
            'es_contacto_principal' => true,
        ]);

        // Verificar que el mismo usuario coexiste con ambos perfiles sin conflicto
        $this->assertTrue($user->doctor->is($doctor));
        $this->assertEquals('12345678', $user->doctor->cedula_profesional);

        $this->assertCount(1, $user->patients);
        $this->assertTrue($user->patients->first()->is($patient));
        $this->assertEquals(RelationshipType::TITULAR->value, $user->patients->first()->pivot->relacion);
    }

    public function test_seeder_creates_doctor_only_and_dual_doctor_patient_users(): void
    {
        $this->seed();

        // 1. Médico con solo rol doctor (Dr. Jessie)
        $doctorOnly = User::where('email', 'jessie@gmail.com')->first();
        $this->assertNotNull($doctorOnly);
        $this->assertTrue($doctorOnly->hasRole('doctor'));
        $this->assertFalse($doctorOnly->hasRole('patient'));
        $this->assertNotNull($doctorOnly->doctor);
        $this->assertEquals('12345678', $doctorOnly->doctor->cedula_profesional);

        // 2. Médico con rol dual: doctor y patient (Dra. Sofía)
        $dualUser = User::where('email', 'sofia.doctor@gmail.com')->first();
        $this->assertNotNull($dualUser);
        $this->assertTrue($dualUser->hasRole('doctor'));
        $this->assertTrue($dualUser->hasRole('patient'));
        $this->assertNotNull($dualUser->doctor);
        $this->assertEquals('23456789', $dualUser->doctor->cedula_profesional);
        $this->assertTrue($dualUser->patients()->wherePivot('relacion', RelationshipType::TITULAR->value)->exists());
    }
}

