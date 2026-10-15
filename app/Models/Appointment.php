<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;

class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentFactory> */
    use HasFactory;

    protected $table = 'citas';

    protected $fillable = [
        'fecha_hora_inicio',
        'fecha_hora_termino',
        'motivo',
        'observaciones_cita',
        'medico_id',
        'cita_estatus_id',
    ];

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

    public function cancel(): void
    {
        Log::info('Cancelar cita en modelo', ['id' => $this->id]);
        $this->cita_estatus_id = CatalogAppointmentStatus::CANCELADA;
        $this->save();
    }
}
