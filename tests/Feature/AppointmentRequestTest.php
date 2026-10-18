<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RelationshipType;
use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AppointmentRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $father;
    private User $doctor;
    private User $receptionist;
    private Patient $child;
    private Patient $strangerChild;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Crear catálogo de estatus
        CatalogAppointmentStatus::factory()->create(['id' => 1, 'nombre' => 'Pendiente']);
        CatalogAppointmentStatus::factory()->create(['id' => 2, 'nombre' => 'Confirmada']);
        CatalogAppointmentStatus::factory()->create(['id' => 3, 'nombre' => 'Cancelada']);
        CatalogAppointmentStatus::factory()->create(['id' => 4, 'nombre' => 'Atendida']);
        CatalogAppointmentStatus::factory()->create(['id' => 5, 'nombre' => 'No asistió']);
        CatalogAppointmentStatus::factory()->create(['id' => 6, 'nombre' => 'En sala de espera']);

        $this->doctor = User::factory()->create(['nombre' => 'Gregory', 'primer_apellido' => 'House', 'email' => 'house@example.com']);
        $this->doctor->assignRole('doctor');

        $this->receptionist = User::factory()->create(['nombre' => 'Recepcionista', 'primer_apellido' => 'Carla']);
        $this->receptionist->assignRole('receptionist');

        $this->father = User::factory()->create(['nombre' => 'Homero', 'primer_apellido' => 'Simpson', 'email' => 'homero@example.com']);
        $this->father->assignRole('patient');

        $this->child = Patient::create([
            'nombre' => 'Bart',
            'primer_apellido' => 'Simpson',
        ]);
        $this->father->patients()->attach($this->child->id, [
            'relacion' => RelationshipType::HIJO->value,
            'es_contacto_principal' => true,
        ]);

        $this->strangerChild = Patient::create([
            'nombre' => 'Milhouse',
            'primer_apellido' => 'Van Houten',
        ]);
    }

    public function test_father_can_request_appointment_for_his_child(): void
    {
        Sanctum::actingAs($this->father);

        $preferredDate = Carbon::tomorrow()->format('Y-m-d');

        $response = $this->postJson('/api/appointments/request', [
            'paciente_id' => $this->child->id,
            'medico_id' => $this->doctor->id,
            'motivo' => 'Fiebre y dolor de garganta',
            'fecha_preferida' => $preferredDate,
            'turno_preferido' => 'tarde',
            'observaciones' => 'Favor de considerar horario después de las 4 PM',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.motivo', 'Fiebre y dolor de garganta')
            ->assertJsonPath('data.turno_preferido', 'tarde')
            ->assertJsonPath('data.estatus.id', CatalogAppointmentStatus::PENDIENTE)
            ->assertJsonPath('data.fecha_hora_inicio', null)
            ->assertJsonPath('data.fecha_hora_termino', null);

        $this->assertDatabaseHas('citas', [
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
            'motivo' => 'Fiebre y dolor de garganta',
            'turno_preferido' => 'tarde',
        ]);

        $activity = Activity::where('log_name', 'citas')
            ->where('description', 'Solicitud de cita médica registrada')
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($this->father->id, $activity->causer_id);
    }

    public function test_father_cannot_request_appointment_for_unrelated_patient(): void
    {
        Sanctum::actingAs($this->father);

        $response = $this->postJson('/api/appointments/request', [
            'paciente_id' => $this->strangerChild->id,
            'medico_id' => $this->doctor->id,
            'motivo' => 'Chequeo general',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['paciente_id']);
    }

    public function test_receptionist_can_confirm_pending_appointment_request(): void
    {
        // 1. Padre solicita cita
        Sanctum::actingAs($this->father);
        $requestResponse = $this->postJson('/api/appointments/request', [
            'paciente_id' => $this->child->id,
            'medico_id' => $this->doctor->id,
            'motivo' => 'Revisión dental',
        ]);
        $appointmentId = $requestResponse->json('data.id');

        // 2. Recepcionista confirma cita asignando horario
        Sanctum::actingAs($this->receptionist);

        $startTime = Carbon::tomorrow()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s');
        $endTime = Carbon::tomorrow()->setHour(10)->setMinute(30)->format('Y-m-d H:i:s');

        $confirmResponse = $this->patchJson("/api/appointments/{$appointmentId}/confirm", [
            'fecha_hora_inicio' => $startTime,
            'fecha_hora_termino' => $endTime,
            'observaciones' => 'Confirmada vía llamada telefónica con el tutor',
        ]);

        $confirmResponse->assertStatus(200)
            ->assertJsonPath('data.id', $appointmentId)
            ->assertJsonPath('data.estatus.id', CatalogAppointmentStatus::CONFIRMADA)
            ->assertJsonPath('data.fecha_hora_inicio', $startTime)
            ->assertJsonPath('data.fecha_hora_termino', $endTime);

        $this->assertDatabaseHas('citas', [
            'id' => $appointmentId,
            'cita_estatus_id' => CatalogAppointmentStatus::CONFIRMADA,
            'fecha_hora_inicio' => $startTime,
        ]);

        $activity = Activity::where('log_name', 'citas')
            ->where('description', 'Cita médica confirmada con horario asignado')
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($this->receptionist->id, $activity->causer_id);
    }

    public function test_receptionist_cannot_confirm_appointment_if_doctor_has_collision(): void
    {
        $startTime = Carbon::tomorrow()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s');
        $endTime = Carbon::tomorrow()->setHour(12)->setMinute(0)->format('Y-m-d H:i:s');

        // Crear una cita ya activa en ese horario
        Appointment::create([
            'medico_id' => $this->doctor->id,
            'fecha_hora_inicio' => $startTime,
            'fecha_hora_termino' => $endTime,
            'cita_estatus_id' => CatalogAppointmentStatus::CONFIRMADA,
        ]);

        // Crear una solicitud pendiente
        $pendingAppointment = Appointment::create([
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
            'motivo' => 'Consulta urgente',
        ]);

        Sanctum::actingAs($this->receptionist);

        // Intentar confirmar la cita en un horario que choca (11:30 a 12:30)
        $conflictingStart = Carbon::tomorrow()->setHour(11)->setMinute(30)->format('Y-m-d H:i:s');
        $conflictingEnd = Carbon::tomorrow()->setHour(12)->setMinute(30)->format('Y-m-d H:i:s');

        $response = $this->patchJson("/api/appointments/{$pendingAppointment->id}/confirm", [
            'fecha_hora_inicio' => $conflictingStart,
            'fecha_hora_termino' => $conflictingEnd,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fecha_hora_inicio']);
    }

    public function test_patient_only_sees_their_own_appointments_in_index(): void
    {
        // Cita para el hijo de Homero
        $homeroAppointment = Appointment::create([
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
            'motivo' => 'Cita de Bart',
        ]);
        $homeroAppointment->patients()->attach($this->child->id);

        // Cita para Milhouse (ajeno a Homero)
        $milhouseAppointment = Appointment::create([
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
            'motivo' => 'Cita de Milhouse',
        ]);
        $milhouseAppointment->patients()->attach($this->strangerChild->id);

        Sanctum::actingAs($this->father);

        $response = $this->getJson('/api/appointments');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $homeroAppointment->id)
            ->assertJsonPath('data.0.motivo', 'Cita de Bart');
    }

    public function test_receptionist_can_check_in_patient_when_patient_did_not_confirm_beforehand(): void
    {
        $appointment = Appointment::create([
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
            'fecha_hora_inicio' => Carbon::now()->addHour()->format('Y-m-d H:i:s'),
            'fecha_hora_termino' => Carbon::now()->addHours(2)->format('Y-m-d H:i:s'),
            'motivo' => 'Revisión general',
        ]);
        $appointment->patients()->attach($this->child->id);

        Sanctum::actingAs($this->receptionist);

        $response = $this->patchJson("/api/appointments/{$appointment->id}/check-in");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $appointment->id)
            ->assertJsonPath('data.estatus.id', CatalogAppointmentStatus::EN_SALA_DE_ESPERA);

        $this->assertDatabaseHas('citas', [
            'id' => $appointment->id,
            'cita_estatus_id' => CatalogAppointmentStatus::EN_SALA_DE_ESPERA,
        ]);

        $activity = Activity::where('log_name', 'citas')
            ->where('description', 'Paciente en sala de espera (Check-in presencial)')
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($this->receptionist->id, $activity->causer_id);
    }

    public function test_receptionist_can_check_in_patient_from_confirmed_status(): void
    {
        $appointment = Appointment::create([
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::CONFIRMADA,
            'fecha_hora_inicio' => Carbon::now()->addHour()->format('Y-m-d H:i:s'),
            'fecha_hora_termino' => Carbon::now()->addHours(2)->format('Y-m-d H:i:s'),
            'motivo' => 'Control pediátrico',
        ]);
        $appointment->patients()->attach($this->child->id);

        Sanctum::actingAs($this->receptionist);

        $response = $this->patchJson("/api/appointments/{$appointment->id}/check-in");

        $response->assertStatus(200)
            ->assertJsonPath('data.estatus.id', CatalogAppointmentStatus::EN_SALA_DE_ESPERA);
    }

    public function test_doctor_can_attend_appointment_directly_from_waiting_room(): void
    {
        $appointment = Appointment::create([
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::EN_SALA_DE_ESPERA,
            'fecha_hora_inicio' => Carbon::now()->format('Y-m-d H:i:s'),
            'fecha_hora_termino' => Carbon::now()->addHour()->format('Y-m-d H:i:s'),
            'motivo' => 'Dolor de estómago',
        ]);
        $appointment->patients()->attach($this->child->id);

        Sanctum::actingAs($this->doctor);

        $consultationPayload = [
            'paciente_id' => $this->child->id,
            'cita_id' => $appointment->id,
            'motivo' => 'Dolor de estómago agudo',
            'diagnostico' => 'Gastroenteritis leve',
            'tratamiento' => 'Hidratación y suero oral',
            'peso' => 35.5,
        ];

        $response = $this->postJson('/api/consultations', $consultationPayload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('citas', [
            'id' => $appointment->id,
            'cita_estatus_id' => CatalogAppointmentStatus::ATENDIDA,
        ]);
    }

    public function test_receptionist_can_mark_appointment_as_no_show(): void
    {
        $appointment = Appointment::create([
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::CONFIRMADA,
            'fecha_hora_inicio' => Carbon::yesterday()->format('Y-m-d H:i:s'),
            'fecha_hora_termino' => Carbon::yesterday()->addHour()->format('Y-m-d H:i:s'),
            'motivo' => 'Cita pasada no asistida',
        ]);
        $appointment->patients()->attach($this->child->id);

        Sanctum::actingAs($this->receptionist);

        $response = $this->patchJson("/api/appointments/{$appointment->id}/no-show");

        $response->assertStatus(200)
            ->assertJsonPath('data.estatus.id', CatalogAppointmentStatus::NO_ASISTIO);

        $this->assertDatabaseHas('citas', [
            'id' => $appointment->id,
            'cita_estatus_id' => CatalogAppointmentStatus::NO_ASISTIO,
        ]);
    }

    public function test_doctor_sees_patients_in_waiting_room(): void
    {
        // Cita en sala de espera para el Dr. House
        $inWaitingRoom = Appointment::create([
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::EN_SALA_DE_ESPERA,
            'motivo' => 'Paciente ya en clínica',
        ]);
        $inWaitingRoom->patients()->attach($this->child->id);

        // Otra cita del Dr. House aún pendiente en casa
        $stillPending = Appointment::create([
            'medico_id' => $this->doctor->id,
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
            'motivo' => 'Paciente en camino',
        ]);
        $stillPending->patients()->attach($this->strangerChild->id);

        Sanctum::actingAs($this->doctor);

        $response = $this->getJson('/api/appointments?status=6');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $inWaitingRoom->id)
            ->assertJsonPath('data.0.estatus.id', CatalogAppointmentStatus::EN_SALA_DE_ESPERA);
    }
}
