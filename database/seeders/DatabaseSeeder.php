<?php

declare(strict_types=1);

namespace Database\Seeders;

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
        // 1. Catálogo completo de estatus de citas
        CatalogAppointmentStatus::firstOrCreate(['id' => 1], ['nombre' => 'Pendiente']);
        CatalogAppointmentStatus::firstOrCreate(['id' => 2], ['nombre' => 'Confirmada']);
        CatalogAppointmentStatus::firstOrCreate(['id' => 3], ['nombre' => 'Cancelada']);
        CatalogAppointmentStatus::firstOrCreate(['id' => 4], ['nombre' => 'Atendida']);
        CatalogAppointmentStatus::firstOrCreate(['id' => 5], ['nombre' => 'No asistió']);
        CatalogAppointmentStatus::firstOrCreate(['id' => 6], ['nombre' => 'En sala de espera']);

        // 2. Catálogo de roles y permisos
        $this->call(RolesAndPermissionsSeeder::class);

        // 3. Usuarios base con credenciales estandarizadas (Contraseña: 123123123)
        $password = Hash::make('123123123');

        // Rol: Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'curp' => 'ADMG800101HDFR0001',
                'nombre' => 'Administrador',
                'primer_apellido' => 'General',
                'segundo_apellido' => 'Sistema',
                'password' => $password,
            ]
        );
        $admin->assignRole('admin');

        // Rol: Doctor (Jessie)
        $doctor = User::firstOrCreate(
            ['email' => 'jessie@gmail.com'],
            [
                'curp' => 'DOCJ850315HDFR0002',
                'nombre' => 'Jessie',
                'primer_apellido' => 'García',
                'segundo_apellido' => 'López',
                'password' => $password,
            ]
        );
        $doctor->assignRole('doctor');

        // Rol: Recepcionista (Josue)
        $receptionist = User::firstOrCreate(
            ['email' => 'josue@gmail.com'],
            [
                'curp' => 'RECJ900820HDFR0003',
                'nombre' => 'Josue',
                'primer_apellido' => 'Mendoza',
                'segundo_apellido' => 'Hernández',
                'password' => $password,
            ]
        );
        $receptionist->assignRole('receptionist');

        // Rol: Paciente / Tutor (Carlos Gómez - a cargo de su hijo y madre)
        $carlos = User::firstOrCreate(
            ['email' => 'carlos.tutor@gmail.com'],
            [
                'curp' => 'GOHC820512HDFR0001',
                'nombre' => 'Carlos',
                'primer_apellido' => 'Gómez',
                'segundo_apellido' => 'Hernández',
                'password' => $password,
            ]
        );
        $carlos->assignRole('patient');

        // Rol: Paciente / Madre (Ana Martínez - madre de Mateo)
        $ana = User::firstOrCreate(
            ['email' => 'ana.madre@gmail.com'],
            [
                'curp' => 'MARA860614MDFR0005',
                'nombre' => 'Ana',
                'primer_apellido' => 'Martínez',
                'segundo_apellido' => 'Sánchez',
                'password' => $password,
            ]
        );
        $ana->assignRole('patient');

        // Rol: Paciente Adulto Independiente (Roberto Pérez)
        $roberto = User::firstOrCreate(
            ['email' => 'roberto.adulto@gmail.com'],
            [
                'curp' => 'PECR881015HDFR0004',
                'nombre' => 'Roberto',
                'primer_apellido' => 'Pérez',
                'segundo_apellido' => 'Castillo',
                'password' => $password,
            ]
        );
        $roberto->assignRole('patient');

        // 4. Seeder de Pacientes y vínculos familiares
        $this->call(PatientSeeder::class);

        // 5. Seeder de Citas médicas con múltiples estatus y consultas asociadas
        $this->call(AppointmentSeeder::class);
    }
}
