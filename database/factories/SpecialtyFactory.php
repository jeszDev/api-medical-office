<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Specialty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Specialty>
 */
class SpecialtyFactory extends Factory
{
    protected $model = Specialty::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->randomElement([
                'Medicina General',
                'Pediatría',
                'Ginecología y Obstetricia',
                'Cardiología',
                'Dermatología',
                'Traumatología y Ortopedia',
                'Medicina Interna',
                'Oftalmología',
                'Otorrinolaringología',
                'Neurología',
                'Psiquiatría',
                'Urología',
                'Endocrinología',
                'Gastroenterología',
                'Neumología',
            ]),
            'descripcion' => fake()->optional(0.7)->sentence(),
        ];
    }
}

