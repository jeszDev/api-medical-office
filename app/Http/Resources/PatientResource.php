<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\RelationshipType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
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
            'curp' => $this->curp,
            'nombre' => $this->nombre,
            'primer_apellido' => $this->primer_apellido,
            'segundo_apellido' => $this->segundo_apellido,
            'fecha_nacimiento' => $this->fecha_nacimiento
                ? Carbon::parse($this->fecha_nacimiento)->format('d/m/Y')
                : null,
            'telefono' => $this->telefono,
            'correo_electronico' => $this->correo_electronico,
            'observaciones' => $this->observaciones,
            'creado_el' => $this->created_at
                ? Carbon::parse($this->created_at)->format('d/m/Y')
                : null,

            // campos calculados
            'nombre_completo' => $this->full_name,
            'edad' => $this->fecha_nacimiento
                ? Carbon::parse($this->fecha_nacimiento)->age
                : null,

            // relación con tutor o cuenta familiar (tabla pivote paciente_usuario)
            'vinculo_familiar' => $this->whenPivotLoaded('paciente_usuario', function () {
                $type = RelationshipType::tryFrom((string) $this->pivot->relacion);

                return [
                    'relacion' => $this->pivot->relacion,
                    'relacion_texto' => $type?->label() ?? $this->pivot->relacion,
                    'es_contacto_principal' => (bool) $this->pivot->es_contacto_principal,
                ];
            }),
        ];
    }
}
