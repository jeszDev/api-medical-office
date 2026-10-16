<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consultation extends Model
{
    /** @use HasFactory<\Database\Factories\ConsultationFactory> */
    use HasFactory;

    protected $table = 'consultas';

    protected $fillable = [
        'motivo',
        'exploracion_fisica',
        'diagnostico',
        'tratamiento',
        'observaciones',
        'notas',
        'peso',
        'altura',
        'temperatura',
        'presion_arterial',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'cita_id',
        'paciente_id',
        'medico_id',
        'folio_receta',
        'receta_pdf_path',
        'receta_generada_el',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'peso' => 'decimal:2',
            'altura' => 'decimal:2',
            'temperatura' => 'decimal:2',
            'frecuencia_cardiaca' => 'integer',
            'frecuencia_respiratoria' => 'integer',
            'receta_generada_el' => 'datetime',
        ];
    }

    /**
     * Genera un folio consecutivo anual para recetas médicas en formato REC-YYYY-0001.
     */
    public static function generateUniquePrescriptionFolio(?string $year = null): string
    {
        $year = $year ?? date('Y');
        $prefix = "REC-{$year}-";

        $lastConsultation = self::where('folio_receta', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        if ($lastConsultation && preg_match('/^REC-\d{4}-(\d+)$/', (string) $lastConsultation->folio_receta, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        } else {
            $nextSequence = 1;
        }

        $candidate = sprintf('%s%04d', $prefix, $nextSequence);

        while (self::where('folio_receta', $candidate)->exists()) {
            $nextSequence++;
            $candidate = sprintf('%s%04d', $prefix, $nextSequence);
        }

        return $candidate;
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'paciente_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medico_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'cita_id');
    }
}
