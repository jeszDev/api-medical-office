<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SpecialtyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Specialty extends Model
{
    /** @use HasFactory<SpecialtyFactory> */
    use HasFactory;

    protected $table = 'especialidades';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Médicos que cuentan con esta especialidad.
     *
     * @return BelongsToMany<Doctor, $this>
     */
    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'especialidad_medico', 'especialidad_id', 'medico_id')
            ->withPivot(['cedula_especialidad', 'universidad_especialidad', 'es_principal'])
            ->withTimestamps();
    }
}

