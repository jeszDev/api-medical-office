<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\CatalogAppointmentStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        CatalogAppointmentStatus::firstOrCreate(['id' => 1], ['nombre' => 'Pendiente']);
        CatalogAppointmentStatus::firstOrCreate(['id' => 2], ['nombre' => 'Confirmada']);
        CatalogAppointmentStatus::firstOrCreate(['id' => 3], ['nombre' => 'Cancelada']);
        CatalogAppointmentStatus::firstOrCreate(['id' => 4], ['nombre' => 'Atendida']);
        CatalogAppointmentStatus::firstOrCreate(['id' => 5], ['nombre' => 'No asistió']);
        CatalogAppointmentStatus::firstOrCreate(['id' => 6], ['nombre' => 'En sala de espera']);

        $this->call([
            RolesAndPermissionsSeeder::class,
            PatientSeeder::class,
            // AppointmentSeeder::class,
        ]);

        $doctor = User::factory()->create([
            'name' => 'Jessie',
            'email' => 'jessie@gmail.com',
            'password' => Hash::make('123123123'),
        ]);
        $doctor->assignRole('doctor');

        $receptionist = User::factory()->create([
            'name' => 'Josue',
            'email' => 'josue@gmail.com',
            'password' => Hash::make('123123123'),
        ]);
        $receptionist->assignRole('receptionist');
    }
}
