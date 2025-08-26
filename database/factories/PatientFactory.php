<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->firstName(),
            'primer_apellido' => $this->faker->lastName(),
            'segundo_apellido' => $this->faker->optional(0.7)->lastName(),
            'fecha_nacimiento' => optional($this->faker->optional(0.8)->dateTimeBetween('-80 years', '-18 years'))->format('Y-m-d'),
            'telefono' => $this->faker->optional('0.9')->phoneNumber(),
            'correo_electronico' => $this->faker->optional(0.7)->safeEmail(),
            'observaciones' => $this->faker->optional(0.4)->text(200),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    /**
     * Indica que el paciente tiene todos los campos obligatorios completos
     */
    public function completo(): static
    {
        return $this->state(fn (array $attributes) => [
            'segundo_apellido' => $this->faker->lastName(),
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
            'telefono' => $this->faker->phoneNumber(),
            'correo_electronico' => $this->faker->safeEmail(),
            'observaciones' => $this->faker->text(200),
        ]);
    }

    /**
     * Indica que el paciente es menor de edad
     */
    public function menorDeEdad(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-17 years', '-1 year')->format('Y-m-d'),
        ]);
    }

    /**
     * Indica que el paciente es mayor de edad
     */
    public function mayorDeEdad(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
        ]);
    }

    /**
     * Indica que el paciente tiene alergias en observaciones
     */
    public function conAlergias(): static
    {
        $alergias = [
            'Alérgico a penicilina',
            'Alergia a frutos secos',
            'Alergia al polen',
            'Alergia a mariscos',
            'Alergia a la lactosa',
            'Alergia a antiinflamatorios',
            'Alergia a ácaros del polvo'
        ];

        return $this->state(fn (array $attributes) => [
            'observaciones' => 'Paciente con alergia: ' . $this->faker->randomElement($alergias) . '. ' . $this->faker->sentence(),
        ]);
    }
}
