<?php

declare(strict_types=1);

namespace App\Http\Requests\Consultations;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $patient = $this->route('patient');

        if ($patient instanceof \App\Models\Patient) {
            $this->mergeIfMissing(['paciente_id' => $patient->id]);
        } elseif (is_numeric($patient)) {
            $this->mergeIfMissing(['paciente_id' => (int) $patient]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'cita_id' => ['nullable', 'integer', 'exists:citas,id'],
            'motivo' => ['required', 'string', 'max:2000'],
            'exploracion_fisica' => ['nullable', 'string'],
            'diagnostico' => ['required', 'string', 'max:2000'],
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
            'paciente_id' => 'paciente',
            'cita_id' => 'cita',
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
