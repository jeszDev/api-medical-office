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
        $inicio = $this->faker->dateTimeBetween('+1 days', '+1 month');
        $termino = (clone $inicio)->modify('+30 minutes');

        return [
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_termino' => $termino,
            'motivo' => $this->faker->sentence,
            'observaciones_cita' => $this->faker->sentence,
            'medico_id' => 1,
            'cita_estatus_id' => 1,
        ];
    }
}
