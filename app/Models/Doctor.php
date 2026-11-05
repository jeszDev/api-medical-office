<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DoctorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Doctor extends Model
{
    /** @use HasFactory<DoctorFactory> */
    use HasFactory;

    protected $table = 'medicos';

    protected $fillable = [
        'user_id',
        'cedula_profesional',
        'universidad',
        'telefono_consultorio',
        'direccion_consultorio',
        'firma_digital_path',
    ];

    /**
     * Cuenta de usuario asociada a este perfil médico.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Especialidades médicas acreditadas por este profesional.
     *
     * @return BelongsToMany<Specialty, $this>
     */
    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'especialidad_medico', 'medico_id', 'especialidad_id')
            ->withPivot(['cedula_especialidad', 'universidad_especialidad', 'es_principal'])
            ->withTimestamps();
    }

    /**
     * Obtiene la especialidad principal del médico si está definida.
     */
    public function primarySpecialty(): ?Specialty
    {
        return $this->specialties()->wherePivot('es_principal', true)->first();
    }
}

