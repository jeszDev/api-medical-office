<?php

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

        CatalogAppointmentStatus::factory()->create([
            'nombre' => 'Pendiente',
        ]);
        CatalogAppointmentStatus::factory()->create([
            'nombre' => 'Confirmada',
        ]);
        CatalogAppointmentStatus::factory()->create([
            'nombre' => 'Cancelada',
        ]);
        CatalogAppointmentStatus::factory()->create([
            'nombre' => 'Atendida',
        ]);
        CatalogAppointmentStatus::factory()->create([
            'nombre' => 'No asistió',
        ]);

        $this->call([
            RolesAndPermissionsSeeder::class,
            PatientSeeder::class,
            // AppointmentSeeder::class,
        ]);

        $user = User::factory()->create([
            'name' => 'Jessie Josue',
            'email' => 'jessie@gmail.com',
            'password' => Hash::make('123123123'),
        ]);
        $user->assignRole('doctor');
    }
}
