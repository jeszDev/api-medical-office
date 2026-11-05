<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Doctor
 */
class DoctorResource extends JsonResource
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
            'cedula_profesional' => $this->cedula_profesional,
            'universidad' => $this->universidad,
            'telefono_consultorio' => $this->telefono_consultorio,
            'direccion_consultorio' => $this->direccion_consultorio,
            'firma_digital_path' => $this->firma_digital_path,
            'especialidades' => SpecialtyResource::collection($this->whenLoaded('specialties')),
        ];
    }
}

