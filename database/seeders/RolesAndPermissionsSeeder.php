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
        // Limpia cache de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Permisos
        |--------------------------------------------------------------------------
        */

        $permissions = [
            // Pacientes
            'patients.view',
            'patients.create',
            'patients.update',
            'patients.delete',

            // Citas
            'appointments.view',
            'appointments.create',
            'appointments.cancel',
            'appointments.confirm',
            'appointments.complete',

            // Consultas Médicas
            'consultations.view',
            'consultations.create',
            'consultations.update',
            'consultations.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $receptionist = Role::firstOrCreate(['name' => 'receptionist']);
        $doctor = Role::firstOrCreate(['name' => 'doctor']);

        /*
        |--------------------------------------------------------------------------
        | Asignación de permisos
        |--------------------------------------------------------------------------
        */

        $receptionist->syncPermissions([
            'patients.view',
            'patients.create',
            'patients.update',

            'appointments.view',
            'appointments.create',
            'appointments.cancel',
            'appointments.confirm',
        ]);

        $doctor->syncPermissions([
            'patients.view',

            'appointments.view',
            'appointments.complete',

            'consultations.view',
            'consultations.create',
            'consultations.update',
        ]);
    }
}
