<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory;

    protected $table = 'pacientes';

    protected $fillable = [
        'curp',
        'nombre',
        'primer_apellido',
        'segundo_apellido',
        'fecha_nacimiento',
        'telefono',
        'correo_electronico',
        'observaciones',
    ];

    public function appointments(): BelongsToMany
    {
        return $this->belongsToMany(Appointment::class, 'cita_paciente', 'paciente_id', 'cita_id')
            ->withPivot(['observaciones'])
            ->withTimestamps();
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->nombre} {$this->primer_apellido} {$this->segundo_apellido}")
        );
    }
}
