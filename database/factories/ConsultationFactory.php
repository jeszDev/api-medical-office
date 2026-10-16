<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consultation>
 */
class ConsultationFactory extends Factory
{
    protected $model = Consultation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'motivo' => $this->faker->sentence(4),
            'exploracion_fisica' => 'Paciente alerta, orientado en tiempo y espacio. Faringe normal, ruidos cardiacos rítmicos sin soplos, abdomen blando no doloroso.',
            'diagnostico' => $this->faker->sentence(3),
            'tratamiento' => 'Tratamiento sintomático, reposo relativo e hidratación constante.',
            'observaciones' => $this->faker->optional(0.5)->sentence(),
            'notas' => $this->faker->optional(0.3)->sentence(),
            'peso' => $this->faker->randomFloat(2, 20, 95),
            'altura' => $this->faker->randomFloat(2, 1.10, 1.90),
            'temperatura' => $this->faker->randomFloat(1, 36.0, 38.5),
            'presion_arterial' => $this->faker->randomElement(['120/80', '110/70', '130/85', '115/75']),
            'frecuencia_cardiaca' => $this->faker->numberBetween(65, 95),
            'frecuencia_respiratoria' => $this->faker->numberBetween(14, 20),
            'cita_id' => null,
            'paciente_id' => Patient::factory(),
            'medico_id' => User::factory(),
        ];
    }
}
