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
            'fecha' => date('d-m-Y', strtotime($this->fecha)),
            'hora_inicio' => $this->hora_inicio,
            'hora_fin' => $this->hora_fin,
            'motivo' => $this->motivo,
            'observaciones' => $this->observaciones,
            'medico_id' => $this->medico_id,
            'estatus' => $this->status->nombre,
            'pacientes' => PatientResource::collection($this->whenLoaded('patients')),
        ];
    }
}
