<?php

declare(strict_types=1);

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
            'folio' => $this->folio,
            'fecha_hora_inicio' => $this->fecha_hora_inicio,
            'fecha_hora_termino' => $this->fecha_hora_termino,
            'motivo' => $this->motivo,
            'observaciones' => $this->observaciones,
            'medico_id' => $this->medico_id,
            'medico' => $this->whenLoaded('doctor', fn () => $this->doctor?->full_name, $this->doctor?->full_name),
            'cita_estatus_id' => $this->cita_estatus_id,
            'estatus' => $this->whenLoaded('status', fn () => $this->status?->nombre, $this->status?->nombre),
            'pacientes' => PatientResource::collection($this->whenLoaded('patients')),

            'fecha_inicio' => $this->fecha_hora_inicio ? Carbon::parse($this->fecha_hora_inicio)->format('d/m/Y') : null,
            'fecha_termino' => $this->fecha_hora_termino ? Carbon::parse($this->fecha_hora_termino)->format('d/m/Y') : null,
            'hora_inicio' => $this->fecha_hora_inicio ? Carbon::parse($this->fecha_hora_inicio)->format('H:i') : null,
            'hora_termino' => $this->fecha_hora_termino ? Carbon::parse($this->fecha_hora_termino)->format('H:i') : null,
        ];
    }
}
