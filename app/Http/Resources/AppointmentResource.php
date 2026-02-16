<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
            'medico' => $this->doctor->full_name,
            'estatus' => $this->status->nombre,
            'pacientes' => PatientResource::collection($this->whenLoaded('patients')),

            'fecha_inicio' => Carbon::parse($this->fecha_hora_inicio)->format('d/m/Y'),
            'fecha_termino' => Carbon::parse($this->fecha_hora_termino)->format('d/m/Y'),
            'hora_inicio' => Carbon::parse($this->fecha_hora_inicio)->format('H:i'),
            'hora_termino' => Carbon::parse($this->fecha_hora_termino)->format('H:i'),
        ];
    }
}
