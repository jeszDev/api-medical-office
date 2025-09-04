<?php

namespace App\Http\Resources;

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
            'nombre' => $this->nombre,
            'primer_apellido' => $this->primer_apellido,
            'segundo_apellido' => $this->segundo_apellido,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'telefono' => $this->telefono,
            'correo_electronico' => $this->correo_electronico,
            'creado_el' => date('d/m/Y', strtotime($this->created_at)),

            // campos calculados
            'nombre_completo' => trim("{$this->nombre} {$this->primer_apellido} {$this->segundo_apellido}"),
            // relación
            'detalle_cita' => $this->whenPivotLoaded('cita_paciente', function () {
                return [
                    'observaciones' => $this->pivot->observaciones,
                ];
            }),
        ];
    }
}
