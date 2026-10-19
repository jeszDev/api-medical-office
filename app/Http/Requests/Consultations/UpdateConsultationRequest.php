<?php

declare(strict_types=1);

namespace App\Http\Requests\Consultations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'motivo' => ['sometimes', 'required', 'string', 'max:2000'],
            'exploracion_fisica' => ['nullable', 'string'],
            'diagnostico' => ['sometimes', 'required', 'string', 'max:2000'],
            'tratamiento' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
            'notas' => ['nullable', 'string'],

            // Signos vitales
            'peso' => ['nullable', 'numeric', 'between:0,500'],
            'altura' => ['nullable', 'numeric', 'between:0,300'],
            'temperatura' => ['nullable', 'numeric', 'between:30,45'],
            'presion_arterial' => ['nullable', 'string', 'max:20'],
            'frecuencia_cardiaca' => ['nullable', 'integer', 'between:30,250'],
            'frecuencia_respiratoria' => ['nullable', 'integer', 'between:5,100'],
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
            'motivo' => 'motivo de la consulta',
            'exploracion_fisica' => 'exploración física',
            'diagnostico' => 'diagnóstico',
            'tratamiento' => 'tratamiento',
            'observaciones' => 'observaciones',
            'notas' => 'notas clínicas',
            'peso' => 'peso',
            'altura' => 'altura',
            'temperatura' => 'temperatura',
            'presion_arterial' => 'presión arterial',
            'frecuencia_cardiaca' => 'frecuencia cardíaca',
            'frecuencia_respiratoria' => 'frecuencia respiratoria',
        ];
    }
}
