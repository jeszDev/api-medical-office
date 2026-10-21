<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CatalogAppointmentStatus;
use App\Models\ClinicSetting;
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
        // 0. Configuración institucional inicial de la clínica
        ClinicSetting::firstOrCreate(
            ['id' => 1],
            [
                'nombre' => 'Centro Médico Santa María',
                'razon_social' => 'Servicios de Salud Integral S.A. de C.V.',
                'rfc' => 'SSI200101XYZ',
                'direccion' => 'Av. Juárez #1020, Col. Centro, C.P. 06000, Ciudad de México',
                'telefono' => '555-334-5566',
                'telefono_urgencias' => '555-999-8877',
                'correo_electronico' => 'contacto@clinicasantamaria.com',
                'leyenda_receta' => 'Medicamento sujeto a prescripción médica. Consérvese en lugar fresco y seco fuera del alcance de los niños. Presentar esta receta en farmacia.',
            ]
        );

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
            ['name' => 'Administrador General', 'password' => $password]
        );
        $admin->assignRole('admin');

        // Rol: Doctor (Jessie)
        $doctor = User::firstOrCreate(
            ['email' => 'jessie@gmail.com'],
            [
                'name' => 'Dr. Jessie',
                'password' => $password,
                'cedula_profesional' => '12345678',
                'cedula_especialidad' => '87654321',
                'especialidad' => 'Pediatría',
                'institucion_egreso' => 'Universidad Nacional Autónoma de México',
                'telefono_consultorio' => '555-987-6543',
            ]
        );
        $doctor->assignRole('doctor');

        // Rol: Recepcionista (Josue)
        $receptionist = User::firstOrCreate(
            ['email' => 'josue@gmail.com'],
            ['name' => 'Recepcionista Josue', 'password' => $password]
        );
        $receptionist->assignRole('receptionist');

        // Rol: Paciente / Tutor (Carlos Gómez - a cargo de su hijo y madre)
        $carlos = User::firstOrCreate(
            ['email' => 'carlos.tutor@gmail.com'],
            ['name' => 'Carlos Gómez (Tutor)', 'password' => $password]
        );
        $carlos->assignRole('patient');

        // Rol: Paciente / Madre (Ana Martínez - madre de Mateo)
        $ana = User::firstOrCreate(
            ['email' => 'ana.madre@gmail.com'],
            ['name' => 'Ana Martínez (Madre)', 'password' => $password]
        );
        $ana->assignRole('patient');

        // Rol: Paciente Adulto Independiente (Roberto Pérez)
        $roberto = User::firstOrCreate(
            ['email' => 'roberto.adulto@gmail.com'],
            ['name' => 'Roberto Pérez (Paciente)', 'password' => $password]
        );
        $roberto->assignRole('patient');

        // 4. Seeder de Pacientes y vínculos familiares
        $this->call(PatientSeeder::class);

        // 5. Seeder de Citas médicas con múltiples estatus y consultas asociadas
        $this->call(AppointmentSeeder::class);
    }
}
