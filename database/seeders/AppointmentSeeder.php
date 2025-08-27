<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtenemos los 15 pacientes existentes
        $patients = Patient::all();

        // Creamos 30 citas
        Appointment::factory()
            ->count(30)
            ->create()
            ->each(function ($appointment) use ($patients) {
                // Selecciona entre 1 y 3 pacientes aleatorios
                $patientsRandom = $patients->random(rand(1, 3));

                // Asigna los pacientes a la cita con datos extra en la pivote
                $appointment->patients()->attach(
                    $patientsRandom->pluck('id')->toArray(),
                    [
                        'observaciones' => 'Observación por paciente',
                    ]
                );
            });
    }
}
