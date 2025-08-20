<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear un solo paciente
        Patient::factory()->count(5)->create();

        // Crear paciente completo con todos los campos
        Patient::factory()->completo()->count(2)->create();

        // Crear paciente menor de edad
        Patient::factory()->menorDeEdad()->count(2)->create();

        // Crear paciente con alergias
        Patient::factory()->conAlergias()->count(6)->create();

        // Crear múltiples pacientes
        // Patient::factory()->count(50)->create();

        // Combinar estados
        // Patient::factory()
        //     ->mayorDeEdad()
        //     ->conAlergias()
        //     ->create();
    }
}
