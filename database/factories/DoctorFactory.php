<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'cedula_profesional' => (string) fake()->unique()->numberBetween(1000000, 9999999),
            'universidad' => fake()->randomElement([
                'Universidad Nacional Autónoma de México',
                'Instituto Politécnico Nacional',
                'Universidad Autónoma de Guadalajara',
                'Universidad Autónoma de Nuevo León',
                'Tecnológico de Monterrey',
            ]),
            'telefono_consultorio' => fake()->phoneNumber(),
            'direccion_consultorio' => fake()->address(),
            'firma_digital_path' => null,
        ];
    }
}

