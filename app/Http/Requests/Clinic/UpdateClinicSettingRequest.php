<?php

declare(strict_types=1);

namespace App\Http\Requests\Clinic;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClinicSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && (
            $this->user()->can('clinic.update')
            || $this->user()->hasRole('admin')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:200'],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'rfc' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'telefono_urgencias' => ['nullable', 'string', 'max:50'],
            'correo_electronico' => ['nullable', 'email', 'max:150'],
            'logotipo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'leyenda_receta' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nombre' => 'nombre comercial',
            'razon_social' => 'razón social',
            'rfc' => 'RFC',
            'direccion' => 'dirección',
            'telefono' => 'teléfono principal',
            'telefono_urgencias' => 'teléfono de urgencias',
            'correo_electronico' => 'correo electrónico',
            'logotipo' => 'logotipo institucional',
            'leyenda_receta' => 'leyenda sanitaria en recetas',
        ];
    }
}
