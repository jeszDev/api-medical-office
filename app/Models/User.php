<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RelationshipType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'curp',
        'nombre',
        'primer_apellido',
        'segundo_apellido',
        'email',
        'password',
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
            get: fn() => trim("{$this->nombre} {$this->primer_apellido} {$this->segundo_apellido}")
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

    /**
     * Expediente de paciente propio (titular) del usuario, o el principal a su cargo.
     */
    public function patientProfile(): ?Patient
    {
        if ($this->relationLoaded('patients')) {
            return $this->patients->first(fn (Patient $p) => $p->pivot?->relacion === RelationshipType::TITULAR->value)
                ?? $this->patients->first();
        }

        return $this->patients()
            ->wherePivot('relacion', RelationshipType::TITULAR->value)
            ->first() ?? $this->patients()->first();
    }

    /**
     * Perfil médico profesional asociado a esta cuenta de usuario.
     *
     * @return HasOne<Doctor, $this>
     */
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class, 'user_id');
    }

    /**
     * Alias descriptivo para el perfil médico.
     *
     * @return HasOne<Doctor, $this>
     */
    public function doctorProfile(): HasOne
    {
        return $this->doctor();
    }
}
