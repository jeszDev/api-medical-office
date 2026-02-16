<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogAppointmentStatus extends Model
{
    /** @use HasFactory<\Database\Factories\CatalogAppointmentStatusFactory> */
    use HasFactory;

    protected $table = 'catalogo_cita_estatus';

    protected $fillable = ['nombre'];

    public const PENDIENTE = 1;
    public const CONFIRMADA = 2;
    public const CANCELADA = 3;
    public const ATENDIDA = 4;

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'cita_estatus_id');
    }
}
