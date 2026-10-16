<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RelationshipType;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carlosUser = User::where('email', 'carlos.tutor@gmail.com')->first();
        $anaUser = User::where('email', 'ana.madre@gmail.com')->first();
        $robertoUser = User::where('email', 'roberto.adulto@gmail.com')->first();

        // 1. Paciente Titular (Carlos Gómez)
        $carlosPatient = Patient::firstOrCreate(
            ['curp' => 'GOHC820512HDFR0001'],
            [
                'nombre' => 'Carlos',
                'primer_apellido' => 'Gómez',
                'segundo_apellido' => 'Hernández',
                'fecha_nacimiento' => '1982-05-12',
                'telefono' => '5551112233',
                'correo_electronico' => 'carlos.tutor@gmail.com',
                'observaciones' => 'Titular de cuenta familiar. Sin alergias conocidas.',
            ]
        );

        if ($carlosUser) {
            $carlosUser->patients()->syncWithoutDetaching([
                $carlosPatient->id => [
                    'relacion' => RelationshipType::TITULAR->value,
                    'es_contacto_principal' => true,
                ],
            ]);
        }

        // 2. Paciente Menor de Edad (Mateo Gómez - Hijo de Carlos y Ana)
        $mateoPatient = Patient::firstOrCreate(
            ['curp' => 'GOMM170318HDFR0002'],
            [
                'nombre' => 'Mateo',
                'primer_apellido' => 'Gómez',
                'segundo_apellido' => 'Martínez',
                'fecha_nacimiento' => Carbon::now()->subYears(9)->format('Y-m-d'),
                'telefono' => '5551112233',
                'correo_electronico' => null,
                'observaciones' => 'Menor de edad. Alergia confirmada a la penicilina.',
            ]
        );

        if ($carlosUser) {
            $carlosUser->patients()->syncWithoutDetaching([
                $mateoPatient->id => [
                    'relacion' => RelationshipType::HIJO->value,
                    'es_contacto_principal' => true,
                ],
            ]);
        }

        if ($anaUser) {
            $anaUser->patients()->syncWithoutDetaching([
                $mateoPatient->id => [
                    'relacion' => RelationshipType::MADRE->value,
                    'es_contacto_principal' => false,
                ],
            ]);
        }

        // 3. Paciente Adulto Mayor (Rosa Martínez - Abuela / Madre de Carlos)
        $rosaPatient = Patient::firstOrCreate(
            ['curp' => 'MALR480824MDFR0003'],
            [
                'nombre' => 'Rosa',
                'primer_apellido' => 'Martínez',
                'segundo_apellido' => 'López',
                'fecha_nacimiento' => '1948-08-24',
                'telefono' => '5551112233',
                'correo_electronico' => null,
                'observaciones' => 'Paciente de la tercera edad. Hipertensión y control de glucosa. Requiere asistencia.',
            ]
        );

        if ($carlosUser) {
            $carlosUser->patients()->syncWithoutDetaching([
                $rosaPatient->id => [
                    'relacion' => RelationshipType::MADRE->value,
                    'es_contacto_principal' => true,
                ],
            ]);
        }

        // 4. Paciente Adulto Independiente (Roberto Pérez)
        $robertoPatient = Patient::firstOrCreate(
            ['curp' => 'PECR881015HDFR0004'],
            [
                'nombre' => 'Roberto',
                'primer_apellido' => 'Pérez',
                'segundo_apellido' => 'Castillo',
                'fecha_nacimiento' => '1988-10-15',
                'telefono' => '5554445566',
                'correo_electronico' => 'roberto.adulto@gmail.com',
                'observaciones' => 'Paciente hipertenso bajo tratamiento con Losartán 50mg.',
            ]
        );

        if ($robertoUser) {
            $robertoUser->patients()->syncWithoutDetaching([
                $robertoPatient->id => [
                    'relacion' => RelationshipType::TITULAR->value,
                    'es_contacto_principal' => true,
                ],
            ]);
        }

        // 5. Pacientes adicionales para enriquecer la base de datos
        Patient::factory()->count(4)->create();
        Patient::factory()->menorDeEdad()->count(2)->create();
        Patient::factory()->conAlergias()->count(3)->create();
    }
}
