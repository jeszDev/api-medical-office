<?php

declare(strict_types=1);

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && (
            $this->user()->hasAnyRole(['doctor', 'admin'])
            || $this->user()->can('consultations.create')
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
            'name' => ['sometimes', 'string', 'max:255'],
            'cedula_profesional' => ['nullable', 'string', 'max:50'],
            'cedula_especialidad' => ['nullable', 'string', 'max:50'],
            'especialidad' => ['nullable', 'string', 'max:100'],
            'institucion_egreso' => ['nullable', 'string', 'max:255'],
            'telefono_consultorio' => ['nullable', 'string', 'max:50'],
            'firma_digital' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
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
            'name' => 'nombre',
            'cedula_profesional' => 'cédula profesional',
            'cedula_especialidad' => 'cédula de especialidad',
            'especialidad' => 'especialidad',
            'institucion_egreso' => 'institución de egreso',
            'telefono_consultorio' => 'teléfono de consultorio',
            'firma_digital' => 'firma digitalizada',
        ];
    }
}
