<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClinicSettingResource extends JsonResource
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
            'razon_social' => $this->razon_social,
            'rfc' => $this->rfc,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'telefono_urgencias' => $this->telefono_urgencias,
            'correo_electronico' => $this->correo_electronico,
            'logotipo_path' => $this->logotipo_path,
            'logotipo_url' => $this->logotipo_url,
            'leyenda_receta' => $this->leyenda_receta,
            'actualizado_el' => $this->updated_at ? Carbon::parse($this->updated_at)->format('d/m/Y H:i') : null,
        ];
    }
}
