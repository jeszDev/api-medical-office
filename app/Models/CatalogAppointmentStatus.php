<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogAppointmentStatus extends Model
{
    /** @use HasFactory<\Database\Factories\CatalogAppointmentStatusFactory> */
    use HasFactory;

    protected $table = 'catalogo_cita_estatus';

    protected $fillable = ['nombre'];
}
