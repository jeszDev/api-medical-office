<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialties = [
            [
                'nombre' => 'Medicina General',
                'descripcion' => 'Atención médica primaria integral para pacientes de todas las edades.',
            ],
            [
                'nombre' => 'Pediatría',
                'descripcion' => 'Atención médica integral especializada en bebés, niños y adolescentes.',
            ],
            [
                'nombre' => 'Ginecología y Obstetricia',
                'descripcion' => 'Cuidado de la salud reproductiva de la mujer, embarazo, parto y puerperio.',
            ],
            [
                'nombre' => 'Cardiología',
                'descripcion' => 'Diagnóstico y tratamiento de enfermedades del corazón y vasos sanguíneos.',
            ],
            [
                'nombre' => 'Dermatología',
                'descripcion' => 'Diagnóstico y tratamiento de afecciones de la piel, pelo y uñas.',
            ],
            [
                'nombre' => 'Traumatología y Ortopedia',
                'descripcion' => 'Tratamiento de lesiones traumáticas y enfermedades del sistema músculo-esquelético.',
            ],
            [
                'nombre' => 'Medicina Interna',
                'descripcion' => 'Diagnóstico y atención integral de enfermedades complejas en pacientes adultos.',
            ],
            [
                'nombre' => 'Oftalmología',
                'descripcion' => 'Diagnóstico y tratamiento médico-quirúrgico de enfermedades de los ojos.',
            ],
        ];

        foreach ($specialties as $specialty) {
            Specialty::firstOrCreate(
                ['nombre' => $specialty['nombre']],
                ['descripcion' => $specialty['descripcion']]
            );
        }
    }
}

