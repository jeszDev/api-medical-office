<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentFactory> */
    use HasFactory;

    protected $table = 'citas';

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
}
