<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'curp',
        'nombre',
        'primer_apellido',
        'segundo_apellido',
        'email',
        'password',
        'cedula_profesional',
        'cedula_especialidad',
        'especialidad',
        'institucion_egreso',
        'telefono_consultorio',
        'firma_digital_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->nombre} {$this->primer_apellido} {$this->segundo_apellido}")
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->full_name,
            set: function (?string $value) {
                if ($value === null) {
                    return [];
                }
                $parts = explode(' ', trim($value), 3);

                return [
                    'nombre' => $parts[0] ?? '',
                    'primer_apellido' => $parts[1] ?? '',
                    'segundo_apellido' => $parts[2] ?? null,
                ];
            }
        );
    }

    /**
     * Expedientes de pacientes que este usuario gestiona (él mismo, hijos, padres, tutelados).
     */
    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'paciente_usuario', 'user_id', 'paciente_id')
            ->withPivot(['relacion', 'es_contacto_principal'])
            ->withTimestamps();
    }
}
