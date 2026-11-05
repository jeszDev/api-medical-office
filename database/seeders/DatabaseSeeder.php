<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CatalogAppointmentStatus;
use App\Models\Doctor;
use App\Models\Specialty;
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

        // 3. Catálogo de especialidades médicas
        $this->call(SpecialtySeeder::class);

        // 4. Usuarios base con credenciales estandarizadas (Contraseña: 123123123)
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

        // Rol: Solo Médico (Dr. Jessie García)
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
        $doctor->syncRoles(['doctor']);

        $doctorProfile = Doctor::firstOrCreate(
            ['user_id' => $doctor->id],
            [
                'cedula_profesional' => '12345678',
                'universidad' => 'Universidad Nacional Autónoma de México',
                'telefono_consultorio' => '5551234567',
                'direccion_consultorio' => 'Av. Insurgentes Sur 1234, Consultorio 301, Col. Del Valle, CDMX',
            ]
        );

        $pediatria = Specialty::where('nombre', 'Pediatría')->first();
        if ($pediatria && ! $doctorProfile->specialties()->where('especialidad_id', $pediatria->id)->exists()) {
            $doctorProfile->specialties()->attach($pediatria->id, [
                'cedula_especialidad' => '87654321',
                'universidad_especialidad' => 'Hospital Infantil de México Federico Gómez',
                'es_principal' => true,
            ]);
        }

        // Rol Dual: Médico Y Paciente (Dra. Sofía Ramírez)
        $doctorPatient = User::firstOrCreate(
            ['email' => 'sofia.doctor@gmail.com'],
            [
                'curp' => 'RAMS890412MDFR0009',
                'nombre' => 'Sofía',
                'primer_apellido' => 'Ramírez',
                'segundo_apellido' => 'Morales',
                'password' => $password,
            ]
        );
        $doctorPatient->syncRoles(['doctor', 'patient']);

        $sofiaDoctorProfile = Doctor::firstOrCreate(
            ['user_id' => $doctorPatient->id],
            [
                'cedula_profesional' => '23456789',
                'universidad' => 'Universidad Autónoma de Guadalajara',
                'telefono_consultorio' => '5559876543',
                'direccion_consultorio' => 'Av. Universidad 450, Consultorio 102, Guadalajara, Jal.',
            ]
        );

        $medicinaGeneral = Specialty::where('nombre', 'Medicina General')->first();
        if ($medicinaGeneral && ! $sofiaDoctorProfile->specialties()->where('especialidad_id', $medicinaGeneral->id)->exists()) {
            $sofiaDoctorProfile->specialties()->attach($medicinaGeneral->id, [
                'cedula_especialidad' => '98765432',
                'universidad_especialidad' => 'Universidad Autónoma de Guadalajara',
                'es_principal' => true,
            ]);
        }

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
