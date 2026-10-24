<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->full_name,
            'nombre_completo' => $this->full_name,
            'curp' => $this->curp,
            'nombre' => $this->nombre,
            'primer_apellido' => $this->primer_apellido,
            'segundo_apellido' => $this->segundo_apellido,
            'email' => $this->email,
            'cedula_profesional' => $this->cedula_profesional,
            'cedula_especialidad' => $this->cedula_especialidad,
            'especialidad' => $this->especialidad,
            'institucion_egreso' => $this->institucion_egreso,
            'telefono_consultorio' => $this->telefono_consultorio,
            'roles' => $this->getRoleNames(),
            'permissions' => $this->getAllPermissions()->pluck('name'),
        ];
    }
}
