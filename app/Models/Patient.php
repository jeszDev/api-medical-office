<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory, Notifiable;

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

    /**
     * Usuarios responsables, tutores o titulares con acceso a este expediente.
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'paciente_usuario', 'paciente_id', 'user_id')
            ->withPivot(['relacion', 'es_contacto_principal'])
            ->withTimestamps();
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->nombre} {$this->primer_apellido} {$this->segundo_apellido}")
        );
    }

    /**
     * Define the destination address for mail notifications.
     */
    public function routeNotificationForMail(Notification $notification): ?string
    {
        return $this->correo_electronico;
    }

    /**
     * Define the phone number for WhatsApp notifications.
     */
    public function routeNotificationForWhatsApp(Notification $notification): ?string
    {
        return $this->telefono;
    }

    /**
     * Historial de todos los documentos y estudios clínicos del paciente.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ConsultationDocument::class, 'paciente_id');
    }
}
