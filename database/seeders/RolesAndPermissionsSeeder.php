<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpia la caché de permisos de Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | 1. Catálogo Completo de Permisos Atómicos
        |--------------------------------------------------------------------------
        */
        $permissions = [
            // Pacientes
            'patients.view',
            'patients.create',
            'patients.update',
            'patients.delete',

            // Citas Médicas
            'appointments.view',
            'appointments.create',
            'appointments.update',
            'appointments.cancel',
            'appointments.confirm',
            'appointments.complete',
            'appointments.delete',

            // Consultas Médicas
            'consultations.view',
            'consultations.create',
            'consultations.update',
            'consultations.delete',

            // Configuración Institucional de la Clínica
            'clinic.view',
            'clinic.update',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Catálogo de Roles
        |--------------------------------------------------------------------------
        */
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $doctor = Role::firstOrCreate(['name' => 'doctor']);
        $receptionist = Role::firstOrCreate(['name' => 'receptionist']);
        $patient = Role::firstOrCreate(['name' => 'patient']);

        /*
        |--------------------------------------------------------------------------
        | 3. Asignación Coherente de Permisos por Rol
        |--------------------------------------------------------------------------
        */

        // Admin: Control total de la plataforma
        $admin->syncPermissions(Permission::all());

        // Recepcionista: Operación de citas, registro de pacientes y visualización
        $receptionist->syncPermissions([
            'patients.view',
            'patients.create',
            'patients.update',

            'appointments.view',
            'appointments.create',
            'appointments.update',
            'appointments.cancel',
            'appointments.confirm',

            'consultations.view',
            'clinic.view',
        ]);

        // Doctor: Atención clínica de citas y consultas médicas
        $doctor->syncPermissions([
            'patients.view',

            'appointments.view',
            'appointments.complete',

            'consultations.view',
            'consultations.create',
            'consultations.update',
            'clinic.view',
        ]);

        // Paciente / Tutor: Acceso a su propio expediente y citas (filtrado por Policy)
        $patient->syncPermissions([
            'patients.view',
            'patients.create',

            'appointments.view',
            'appointments.create',
            'appointments.cancel',
        ]);
    }
}
