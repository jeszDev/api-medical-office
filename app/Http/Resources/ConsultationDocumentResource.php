<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationDocumentResource extends JsonResource
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
            'consulta_id' => $this->consulta_id,
            'paciente_id' => $this->paciente_id,
            'medico_id' => $this->medico_id,
            'medico' => $this->whenLoaded('uploader', fn () => $this->uploader?->full_name ?? $this->uploader?->name),
            'tipo_documento' => $this->tipo_documento?->value,
            'tipo_documento_etiqueta' => $this->tipo_documento?->label(),
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'nombre_archivo_original' => $this->nombre_archivo_original,
            'mime_type' => $this->mime_type,
            'peso_bytes' => $this->peso_bytes,
            'peso_legible' => $this->peso_formateado,
            'url_descarga' => $this->getDownloadUrl(30),
            'creado_el' => $this->created_at ? Carbon::parse($this->created_at)->format('d/m/Y H:i') : null,
            'eliminado_el' => $this->deleted_at ? Carbon::parse($this->deleted_at)->format('d/m/Y H:i') : null,
        ];
    }
}
