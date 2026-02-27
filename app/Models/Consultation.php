<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $table =  'consultas';

    protected $fillable =  [
        'motivo',
        'exploracion_fisica',
        'diagnostico',
        'tratamiento',
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
    ];
}
