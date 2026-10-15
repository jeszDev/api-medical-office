<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CatalogAppointmentStatus;
use Illuminate\Database\Seeder;

class CatalogAppointmentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            CatalogAppointmentStatus::PENDIENTE => 'Pendiente',
            CatalogAppointmentStatus::CONFIRMADA => 'Confirmada',
            CatalogAppointmentStatus::CANCELADA => 'Cancelada',
            CatalogAppointmentStatus::ATENDIDA => 'Atendida',
        ];

        foreach ($statuses as $id => $nombre) {
            CatalogAppointmentStatus::firstOrCreate(
                ['id' => $id],
                ['nombre' => $nombre]
            );
        }
    }
}
