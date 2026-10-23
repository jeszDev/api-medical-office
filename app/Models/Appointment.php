<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PreferredShift;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentFactory> */
    use HasFactory;

    protected $table = 'citas';

    protected $fillable = [
        'folio',
        'fecha_hora_inicio',
        'fecha_hora_termino',
        'fecha_preferida',
        'turno_preferido',
        'motivo',
        'observaciones',
        'medico_id',
        'cita_estatus_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'turno_preferido' => PreferredShift::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment): void {
            if (empty($appointment->folio)) {
                $year = ! empty($appointment->fecha_hora_inicio)
                    ? date('Y', strtotime((string) $appointment->fecha_hora_inicio))
                    : date('Y');

                $appointment->folio = self::generateUniqueFolio($year);
            }
        });
    }

    /**
     * Genera un folio consecutivo anual en formato CIT-YYYY-0001.
     */
    public static function generateUniqueFolio(?string $year = null): string
    {
        $year = $year ?? date('Y');
        $prefix = "CIT-{$year}-";

        $lastAppointment = self::where('folio', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        if ($lastAppointment && preg_match('/^CIT-\d{4}-(\d+)$/', (string) $lastAppointment->folio, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        } else {
            $nextSequence = 1;
        }

        $candidate = sprintf('%s%04d', $prefix, $nextSequence);

        while (self::where('folio', $candidate)->exists()) {
            $nextSequence++;
            $candidate = sprintf('%s%04d', $prefix, $nextSequence);
        }

        return $candidate;
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medico_id');
    }

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'cita_paciente', 'cita_id', 'paciente_id')
            ->withPivot(['observaciones'])
            ->withTimestamps();
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CatalogAppointmentStatus::class, 'cita_estatus_id');
    }
}
