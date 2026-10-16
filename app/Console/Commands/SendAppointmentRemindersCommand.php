<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Notifications\Appointments\AppointmentReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders 
                            {--date= : Fecha específica a evaluar en formato YYYY-MM-DD (por defecto: mañana)}
                            {--dry-run : Ejecuta la búsqueda sin enviar notificaciones reales}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía notificaciones de recordatorio a los pacientes con citas programadas para el día siguiente';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetDateInput = $this->option('date');
        $targetDate = $targetDateInput ? Carbon::parse($targetDateInput) : Carbon::tomorrow();
        $isDryRun = (bool) $this->option('dry-run');

        $startOfDay = $targetDate->copy()->startOfDay()->format('Y-m-d H:i:s');
        $endOfDay = $targetDate->copy()->endOfDay()->format('Y-m-d H:i:s');

        $this->info("Buscando citas programadas para el día: {$targetDate->format('Y-m-d')} (entre {$startOfDay} y {$endOfDay})...");

        $appointments = Appointment::query()
            ->with(['patients', 'doctor', 'status'])
            ->whereIn('cita_estatus_id', [
                CatalogAppointmentStatus::PENDIENTE,
                CatalogAppointmentStatus::CONFIRMADA,
            ])
            ->whereBetween('fecha_hora_inicio', [$startOfDay, $endOfDay])
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No se encontraron citas pendientes o confirmadas para la fecha seleccionada.');

            return self::SUCCESS;
        }

        $this->info("Se encontraron {$appointments->count()} citas para recordar.");
        $totalSent = 0;

        foreach ($appointments as $appointment) {
            foreach ($appointment->patients as $patient) {
                if ($isDryRun) {
                    $this->line("[DRY-RUN] Notificación simulada para el paciente: {$patient->full_name} ({$patient->correo_electronico}) - Cita ID: {$appointment->id}");
                } else {
                    $patient->notify(new AppointmentReminderNotification($appointment));
                    $this->line("Recordatorio enviado a: {$patient->full_name} ({$patient->correo_electronico}) - Cita ID: {$appointment->id}");
                }
                $totalSent++;
            }
        }

        $this->info("Proceso completado. Total de recordatorios procesados: {$totalSent}.");

        return self::SUCCESS;
    }
}
