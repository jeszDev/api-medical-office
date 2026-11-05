<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Specialty
 */
class SpecialtyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'cedula_especialidad' => $this->when(
                isset($this->pivot?->cedula_especialidad),
                $this->pivot?->cedula_especialidad
            ),
            'universidad_especialidad' => $this->when(
                isset($this->pivot?->universidad_especialidad),
                $this->pivot?->universidad_especialidad
            ),
            'es_principal' => $this->when(
                isset($this->pivot?->es_principal),
                (bool) $this->pivot?->es_principal
            ),
        ];
    }
}

