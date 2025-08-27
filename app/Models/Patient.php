<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory;

    protected $table = 'pacientes';

    protected $fillable = [
        'nombre',
        'primer_apellido',
        'segundo_apellido',
        'fecha_nacimiento',
        'telefono',
        'correo_electronico',
        'observaciones'
    ];

    public function appointment()
    {
        return $this->belongsToMany(Appointment::class, 'cita_paciente', 'id_paciente', 'id_cita')
                    ->withPivot(['es_principal', 'rol', 'observaciones'])
                    ->withTimestamps();
    }
}
