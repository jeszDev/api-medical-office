<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\RelationshipType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientGuardianResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $type = RelationshipType::tryFrom((string) $this->pivot?->relacion);

        return [
            'id' => $this->id,
            'curp' => $this->curp,
            'nombre' => $this->nombre,
            'primer_apellido' => $this->primer_apellido,
            'segundo_apellido' => $this->segundo_apellido,
            'nombre_completo' => $this->full_name,
            'email' => $this->email,
            'relacion' => $this->pivot?->relacion,
            'relacion_texto' => $type?->label() ?? $this->pivot?->relacion,
            'es_contacto_principal' => (bool) ($this->pivot?->es_contacto_principal ?? false),
        ];
    }
}

