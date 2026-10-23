<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PreferredShift;
use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inicio = Carbon::instance($this->faker->dateTimeBetween('+1 days', '+1 month'));
        $termino = (clone $inicio)->addMinutes(30);

        return [
            'fecha_hora_inicio' => $inicio->format('Y-m-d H:i:s'),
            'fecha_hora_termino' => $termino->format('Y-m-d H:i:s'),
            'fecha_preferida' => null,
            'turno_preferido' => null,
            'motivo' => $this->faker->sentence(4),
            'observaciones' => $this->faker->optional(0.6)->sentence(6),
            'medico_id' => User::factory(),
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
        ];
    }

    public function pendiente(): static
    {
        return $this->state(fn () => [
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
        ]);
    }

    public function confirmada(): static
    {
        return $this->state(fn () => [
            'cita_estatus_id' => CatalogAppointmentStatus::CONFIRMADA,
        ]);
    }

    public function enSalaDeEspera(): static
    {
        return $this->state(fn () => [
            'cita_estatus_id' => CatalogAppointmentStatus::EN_SALA_DE_ESPERA,
        ]);
    }

    public function atendida(): static
    {
        return $this->state(fn () => [
            'cita_estatus_id' => CatalogAppointmentStatus::ATENDIDA,
        ]);
    }

    public function cancelada(string $motivo = 'Cancelada por el paciente'): static
    {
        return $this->state(fn () => [
            'cita_estatus_id' => CatalogAppointmentStatus::CANCELADA,
            'observaciones' => $motivo,
        ]);
    }

    public function noAsistio(): static
    {
        return $this->state(fn () => [
            'cita_estatus_id' => CatalogAppointmentStatus::NO_ASISTIO,
            'observaciones' => 'El paciente no se presentó tras la tolerancia.',
        ]);
    }

    public function solicitud(): static
    {
        return $this->state(fn () => [
            'fecha_hora_inicio' => null,
            'fecha_hora_termino' => null,
            'fecha_preferida' => $this->faker->dateTimeBetween('+1 days', '+2 weeks')->format('Y-m-d'),
            'turno_preferido' => $this->faker->randomElement(PreferredShift::cases()),
            'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
        ]);
    }
}
