<?php

declare(strict_types=1);

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
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
            'fecha_hora_inicio' => ['required', 'date'],
            'fecha_hora_termino' => ['required', 'date', 'after:fecha_hora_inicio'],
            'medico_id' => ['required', 'integer', 'exists:users,id'],
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'motivo' => ['nullable', 'string', 'max:1000'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'observaciones_cita' => ['nullable', 'string', 'max:1000'],
            'observaciones_paciente' => ['nullable', 'string', 'max:500'],
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
            'fecha_hora_inicio' => 'fecha y hora de inicio',
            'fecha_hora_termino' => 'fecha y hora de término',
            'medico_id' => 'médico',
            'paciente_id' => 'paciente',
            'motivo' => 'motivo de la cita',
            'observaciones' => 'observaciones de la cita',
            'observaciones_cita' => 'observaciones de la cita',
            'observaciones_paciente' => 'observaciones del paciente',
        ];
    }
}
