<?php

declare(strict_types=1);

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmAppointmentRequest extends FormRequest
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
            'fecha_hora_inicio' => ['nullable', 'date'],
            'fecha_hora_termino' => ['nullable', 'date', 'after:fecha_hora_inicio'],
            'medico_id' => ['nullable', 'integer', 'exists:users,id'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
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
            'medico_id' => 'médico asignado',
            'observaciones' => 'observaciones',
        ];
    }
}
