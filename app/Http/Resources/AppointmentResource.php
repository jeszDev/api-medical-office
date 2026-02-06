<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
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
            'fecha_hora_inicio' => $this->fecha_hora_inicio,
            'fecha_hora_termino' => $this->fecha_hora_termino,
            'motivo' => $this->motivo,
            'observaciones_cita' => $this->observaciones_cita,
            'medico_id' => $this->medico_id,
            'estatus' => $this->status->nombre,
            'pacientes' => PatientResource::collection($this->whenLoaded('patients')),
        ];
    }
}
