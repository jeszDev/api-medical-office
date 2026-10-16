<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationResource extends JsonResource
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
            'motivo' => $this->motivo,
            'exploracion_fisica' => $this->exploracion_fisica,
            'diagnostico' => $this->diagnostico,
            'tratamiento' => $this->tratamiento,
            'observaciones' => $this->observaciones,
            'notas' => $this->notas,

            'signos_vitales' => [
                'peso' => $this->peso,
                'altura' => $this->altura,
                'temperatura' => $this->temperatura,
                'presion_arterial' => $this->presion_arterial,
                'frecuencia_cardiaca' => $this->frecuencia_cardiaca,
                'frecuencia_respiratoria' => $this->frecuencia_respiratoria,
            ],

            'medico_id' => $this->medico_id,
            'medico' => $this->whenLoaded('doctor', fn () => $this->doctor?->full_name),

            'paciente_id' => $this->paciente_id,
            'paciente' => new PatientResource($this->whenLoaded('patient')),

            'cita_id' => $this->cita_id,
            'cita' => new AppointmentResource($this->whenLoaded('appointment')),

            'receta' => [
                'folio' => $this->folio_receta,
                'generada' => ! empty($this->receta_pdf_path),
                'generada_el' => $this->receta_generada_el ? Carbon::parse($this->receta_generada_el)->format('d/m/Y H:i') : null,
                'url' => ! empty($this->receta_pdf_path) ? url("/api/consultations/{$this->id}/prescription") : null,
            ],

            'creado_el' => $this->created_at ? Carbon::parse($this->created_at)->format('d/m/Y H:i') : null,
        ];
    }
}
