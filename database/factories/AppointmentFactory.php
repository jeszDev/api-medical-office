<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fecha = $this->faker->dateTimeBetween('+1 days', '+1 month');

        return [
            'fecha' => $fecha->format('Y-m-d'),
            'hora_inicio' => $fecha->format('H:i:s'),
            'hora_fin' => $fecha->modify('+30 minutes')->format('H:i:s'),
            'motivo' => $this->faker->sentence,
            'observaciones' => $this->faker->sentence,
            'medico_id' => 1,
            'cita_estatus_id' => 1,
        ];
    }
}
