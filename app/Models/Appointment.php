<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function doctor()
    {
        return $this->belongsTo(User::class, 'medico_id');
    }

    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'cita_paciente', 'cita_id', 'paciente_id')
            ->withPivot(['observaciones'])
            ->withTimestamps();
    }

    public function status()
    {
        return $this->belongsTo(CatalogAppointmentStatus::class, 'cita_estatus_id');
    }

    public function cancel(): void
    {
        // if ($this->cita_estatus_id === CatalogAppointmentStatus::CANCELADA) {
        // throw ValidationException::withMessages([
        //     'estatus' => 'La cita ya está cancelada.'
        // ]);
        // }

        Log::info('Llega al modelo a cancelar');

        // $this->update([
        //     'cita_estaus_id' => CatalogAppointmentStatus::CANCELADA
        // ]);
        $this->cita_estatus_id = CatalogAppointmentStatus::CANCELADA;
        $this->save();
    }
}
