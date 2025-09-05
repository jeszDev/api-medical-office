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
            'fecha' => $this->fecha,
            'hora_inicio' => $this->hora_inicio,
            'hora_fin' => $this->hora_fin,
            'motivo' => $this->motivo,
            'observaciones' => $this->observaciones,
            'medico_id' => $this->medico_id,
            'cita_estatus_id' => $this->cita_estatus_id,
            'pacientes' => PatientResource::collection($this->whenLoaded('patients')),
        ];
    }
}
